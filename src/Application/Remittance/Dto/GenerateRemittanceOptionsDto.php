<?php

declare(strict_types=1);

namespace CnabSispag\Application\Remittance\Dto;

/**
 * Opções de geração de remessa controladas pelo sistema integrador.
 *
 * O número sequencial do arquivo (NSA, header pos. 158–166) deve ser gerenciado
 * externamente — por convênio, conta e banco — e informado aqui a cada remessa.
 */
final readonly class GenerateRemittanceOptionsDto
{
    public function __construct(
        public int $fileSequenceNumber = 0,
        public ?int $pixFileSequenceNumber = null,
        public ?int $nonPixFileSequenceNumber = null,
    ) {
    }

    public function forPixFile(): int
    {
        return $this->pixFileSequenceNumber ?? $this->fileSequenceNumber;
    }

    public function forNonPixFile(): int
    {
        return $this->nonPixFileSequenceNumber ?? $this->fileSequenceNumber;
    }
}
