<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Reader;

use CnabSispag\Domain\Return\Entity\ReturnBatch;
use CnabSispag\Domain\Return\Entity\ReturnDetail;
use CnabSispag\Domain\Return\Entity\ReturnFile;
use CnabSispag\Domain\Return\Service\OccurrenceStatusMapper;
use CnabSispag\Domain\Return\ValueObject\Occurrence;
use CnabSispag\Domain\Return\ValueObject\ParsedTaxData;
use CnabSispag\Domain\Return\ValueObject\ReturnSegment;
use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Exception\InvalidLayoutException;
use CnabSispag\Domain\Shared\ValueObject\BankAccount;
use CnabSispag\Domain\Shared\ValueObject\TaxId;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileHeaderRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileTrailerRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Cnab\Encoding\EncodingConverter;
use CnabSispag\Infrastructure\Cnab\IO\CnabLineReader;
use CnabSispag\Infrastructure\Cnab\Parser\RecordParser;
use CnabSispag\Infrastructure\I18n\MessageCatalog;
use CnabSispag\Infrastructure\I18n\OccurrenceTranslator;

final class ItauReturnReader
{
    /** @var list<SegmentType> */
    private const PRIMARY_SEGMENTS = [
        SegmentType::A,
        SegmentType::J,
        SegmentType::O,
        SegmentType::N,
    ];

    public function __construct(
        private readonly EncodingConverter $encoding = new EncodingConverter(),
        private readonly CnabLineReader $lineReader = new CnabLineReader(),
        private readonly RecordParser $parser = new RecordParser(),
        private readonly DetailLayoutResolver $layoutResolver = new DetailLayoutResolver(),
        private readonly OccurrenceStatusMapper $statusMapper = new OccurrenceStatusMapper(),
        private readonly TaxSegmentParser $taxSegmentParser = new TaxSegmentParser(),
        private readonly ReturnFileValidator $validator = new ReturnFileValidator(),
    ) {
    }

    public function read(string $content): ReturnFile
    {
        $lines = $this->lineReader->readLines($this->encoding->normalizeInput($content));

        if ($lines === []) {
            throw new InvalidLayoutException('empty_file', MessageCatalog::get('return.empty_file'));
        }

        $this->assertLineLengths($lines);

        $fileHeader = $this->parseLine(new FileHeaderRecord(), $lines[0]);
        $this->assertReturnFile($fileHeader);

        $batches = [];
        $index = 1;

        while ($index < count($lines) && substr($lines[$index], 7, 1) !== ItauConstants::RECORD_TYPE_FILE_TRAILER) {
            if (substr($lines[$index], 7, 1) !== ItauConstants::RECORD_TYPE_BATCH_HEADER) {
                throw new InvalidLayoutException(
                    'expected_batch_header',
                    MessageCatalog::get('return.expected_batch_header', ['line' => (string) ($index + 1)]),
                );
            }

            $headerLine = $lines[$index];
            $firstDetailLine = $this->findNextDetailLine($lines, $index + 1);
            $paymentMethod = $this->resolvePaymentMethod($headerLine, $firstDetailLine);
            $headerFields = $this->parseLine($this->layoutResolver->resolveBatchHeader($paymentMethod), $headerLine);
            $index++;

            $details = [];
            $currentSegments = [];

            while ($index < count($lines) && substr($lines[$index], 7, 1) === ItauConstants::RECORD_TYPE_DETAIL) {
                $segment = $this->parseDetailSegment($lines[$index], $paymentMethod);
                $segmentType = $segment->segmentType;

                if ($this->isPrimarySegment($segmentType, $lines[$index])) {
                    if ($currentSegments !== []) {
                        $details[] = $this->buildDetail($currentSegments);
                    }
                    $currentSegments = [$segment];
                } elseif ($currentSegments === [] && $segmentType === SegmentType::Z) {
                    $this->attachOrphanAuthentication($details, $segment);
                } else {
                    $currentSegments[] = $segment;
                }

                $index++;
            }

            if ($currentSegments !== []) {
                $details[] = $this->buildDetail($currentSegments);
            }

            if ($index >= count($lines) || substr($lines[$index], 7, 1) !== ItauConstants::RECORD_TYPE_BATCH_TRAILER) {
                throw new InvalidLayoutException(
                    'expected_batch_trailer',
                    MessageCatalog::get('return.expected_batch_trailer', ['line' => (string) ($index + 1)]),
                );
            }

            $trailerFields = $this->parseLine($this->layoutResolver->resolveBatchTrailer($paymentMethod), $lines[$index]);
            $index++;

            $batches[] = new ReturnBatch(
                (int) $headerFields['batchCode'],
                PaymentType::tryFrom((int) $headerFields['paymentType']) ?? PaymentType::Various,
                $paymentMethod,
                $headerFields,
                $details,
                $trailerFields,
            );
        }

        if ($index >= count($lines)) {
            throw new InvalidLayoutException('missing_file_trailer', MessageCatalog::get('return.missing_file_trailer'));
        }

        $fileTrailer = $this->parseLine(new FileTrailerRecord(), $lines[$index]);

        $returnFile = new ReturnFile(
            new TaxId((int) $fileHeader['registrationType'], (string) $fileHeader['registrationNumber']),
            new BankAccount(
                (string) $fileHeader['agency'],
                (string) $fileHeader['account'],
                (string) $fileHeader['accountCheckDigit'],
            ),
            (string) $fileHeader['companyName'],
            $this->parseGenerationDateTime($fileHeader),
            $batches,
            (int) $fileTrailer['batchCount'],
            (int) $fileTrailer['recordCount'],
        );

        $this->validator->validate(
            $returnFile->batchCount,
            $returnFile->recordCount,
            $returnFile->batches,
            $lines,
        );

        return $returnFile;
    }

    /**
     * @param list<string> $lines
     */
    private function findNextDetailLine(array $lines, int $startIndex): ?string
    {
        for ($index = $startIndex; $index < count($lines); $index++) {
            if (substr($lines[$index], 7, 1) === ItauConstants::RECORD_TYPE_DETAIL) {
                return $lines[$index];
            }

            if (substr($lines[$index], 7, 1) === ItauConstants::RECORD_TYPE_BATCH_TRAILER) {
                break;
            }
        }

        return null;
    }

    private function resolvePaymentMethod(string $headerLine, ?string $firstDetailLine): ?PaymentMethod
    {
        $preview = $this->parseLine($this->layoutResolver->resolveBatchHeader(null), $headerLine);
        $formCode = (int) $preview['paymentMethod'];

        if ($formCode !== 16) {
            return PaymentMethod::fromFormCode($formCode);
        }

        if ($firstDetailLine !== null) {
            $segment = substr($firstDetailLine, 13, 1);

            if ($segment === 'O') {
                return PaymentMethod::BarcodeTax;
            }

            if ($segment === 'N') {
                return PaymentMethod::DarfNormal;
            }
        }

        return PaymentMethod::fromFormCode($formCode);
    }

    /**
     * @param list<ReturnDetail> $details
     */
    private function attachOrphanAuthentication(array &$details, ReturnSegment $segment): void
    {
        if ($details === []) {
            return;
        }

        $lastIndex = count($details) - 1;
        $lastDetail = $details[$lastIndex];
        $segments = [...$lastDetail->segments, $segment];
        $authentication = $this->extractAuthentication($segments) ?? $lastDetail->authentication;

        $details[$lastIndex] = new ReturnDetail(
            $lastDetail->primarySegment,
            $segments,
            $lastDetail->occurrences,
            $lastDetail->status,
            $lastDetail->companyDocumentNumber,
            $lastDetail->bankDocumentNumber,
            $authentication,
            $lastDetail->parsedTaxData,
        );
    }

    /**
     * @param list<string> $lines
     */
    private function assertLineLengths(array $lines): void
    {
        foreach ($lines as $lineNumber => $line) {
            if (strlen($line) !== CnabLineReader::LINE_LENGTH) {
                throw new InvalidLayoutException(
                    'invalid_line_length',
                    MessageCatalog::get('return.invalid_line_length', [
                        'line' => (string) ($lineNumber + 1),
                        'expected' => (string) CnabLineReader::LINE_LENGTH,
                        'actual' => (string) strlen($line),
                    ]),
                );
            }
        }
    }

    /** @param array<string, mixed> $fileHeader */
    private function assertReturnFile(array $fileHeader): void
    {
        if ((int) $fileHeader['fileKind'] !== FileKind::Return->value) {
            throw new InvalidLayoutException(
                'not_return_file',
                MessageCatalog::get('return.not_return_file'),
            );
        }

        if ((string) $fileHeader['bankCode'] !== ItauConstants::BANK_CODE) {
            throw new InvalidLayoutException(
                'invalid_bank_code',
                MessageCatalog::get('return.invalid_bank_code'),
            );
        }
    }

    /** @return array<string, mixed> */
    private function parseLine(RecordLayout $layout, string $line): array
    {
        return $this->parser->parse($layout->definition(), $line);
    }

    private function parseDetailSegment(string $line, ?PaymentMethod $paymentMethod): ReturnSegment
    {
        $layout = $this->layoutResolver->resolve($line, $paymentMethod);
        $fields = $this->parseLine($layout, $line);
        $segmentType = $this->resolveSegmentType($line, $paymentMethod);

        return new ReturnSegment(
            $segmentType,
            (int) $fields['recordNumber'],
            $fields,
        );
    }

    private function resolveSegmentType(string $line, ?PaymentMethod $paymentMethod): SegmentType
    {
        $segmentCode = substr($line, 13, 1);

        if ($segmentCode === 'J' && substr($line, 17, 2) === ItauConstants::OPTIONAL_RECORD_J52) {
            return $this->layoutResolver->isJ52PixLine($line, $paymentMethod)
                ? SegmentType::J52Pix
                : SegmentType::J52;
        }

        return SegmentType::from($segmentCode);
    }

    private function isPrimarySegment(SegmentType $segmentType, string $line): bool
    {
        if ($segmentType === SegmentType::J && substr($line, 17, 2) === ItauConstants::OPTIONAL_RECORD_J52) {
            return false;
        }

        return in_array($segmentType, self::PRIMARY_SEGMENTS, true);
    }

    /**
     * @param list<ReturnSegment> $segments
     */
    private function buildDetail(array $segments): ReturnDetail
    {
        $primary = $segments[0];
        $occurrences = $this->extractOccurrences($segments);
        $authentication = $this->extractAuthentication($segments);

        return new ReturnDetail(
            $primary->segmentType,
            $segments,
            $occurrences,
            $this->statusMapper->resolve($occurrences),
            $this->extractDocumentNumber($segments, 'companyDocumentNumber'),
            $this->extractDocumentNumber($segments, 'bankDocumentNumber'),
            $authentication,
            $this->extractParsedTaxData($segments),
        );
    }

    /**
     * @param list<ReturnSegment> $segments
     * @return list<Occurrence>
     */
    private function extractOccurrences(array $segments): array
    {
        $occurrences = [];
        $seen = [];

        foreach ($segments as $segment) {
            if (!isset($segment->fields['occurrences'])) {
                continue;
            }

            foreach (OccurrenceTranslator::parseCodes((string) $segment->fields['occurrences']) as $code) {
                if (isset($seen[$code])) {
                    continue;
                }

                $seen[$code] = true;
                $occurrences[] = new Occurrence($code, OccurrenceTranslator::translate($code));
            }
        }

        return $occurrences;
    }

    /**
     * @param list<ReturnSegment> $segments
     */
    private function extractAuthentication(array $segments): ?string
    {
        foreach ($segments as $segment) {
            if ($segment->segmentType === SegmentType::Z && isset($segment->fields['authentication'])) {
                $value = trim((string) $segment->fields['authentication']);

                return $value !== '' ? $value : null;
            }
        }

        return null;
    }

    /**
     * @param list<ReturnSegment> $segments
     */
    private function extractParsedTaxData(array $segments): ?ParsedTaxData
    {
        foreach ($segments as $segment) {
            if ($segment->segmentType !== SegmentType::N || !isset($segment->fields['taxData'])) {
                continue;
            }

            return $this->taxSegmentParser->parse((string) $segment->fields['taxData']);
        }

        return null;
    }

    /**
     * @param list<ReturnSegment> $segments
     */
    private function extractDocumentNumber(array $segments, string $field): ?string
    {
        foreach ($segments as $segment) {
            if (!isset($segment->fields[$field])) {
                continue;
            }

            $value = trim((string) $segment->fields[$field]);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $fileHeader */
    private function parseGenerationDateTime(array $fileHeader): \DateTimeImmutable
    {
        $date = (string) $fileHeader['generationDate'];
        $time = str_pad((string) $fileHeader['generationTime'], 6, '0', STR_PAD_LEFT);

        return \DateTimeImmutable::createFromFormat('dmY His', $date . ' ' . substr($time, 0, 2) . ':' . substr($time, 2, 2) . ':' . substr($time, 4, 2))
            ?: new \DateTimeImmutable();
    }
}
