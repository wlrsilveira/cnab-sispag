# Planejamento — cnab-sispag

Documentação interna do projeto. Para integradores, use [docs/README.md](../README.md).

## Índice

| Documento | Conteúdo |
|---|---|
| [PLAN.md](./PLAN.md) | Visão geral, escopo v1.0/v2.0, entregáveis |
| [bb-remittance.md](./bb-remittance.md) | Plano detalhado Banco do Brasil (PgtVer03BB) |
| [architecture.md](./architecture.md) | DDD, camadas, API pública, multi-banco |
| [conventions.md](./conventions.md) | Inglês no código, português nas mensagens |
| [segments.md](./segments.md) | Inventário de segmentos e regras de combinação |
| [roadmap.md](./roadmap.md) | Fases, estimativas e status |
| [checklist.md](./checklist.md) | Checklist detalhado tarefa a tarefa |
| [file-map.md](./file-map.md) | Mapa de arquivos do repositório |

## Referências externas

| Banco | Manual |
|---|---|
| Itaú | [SISPAG CNAB 240 v086](https://www.itau.com.br/media/dam/m/3b9688b3979b4016/original/Layout-de-Arquivos_CNAB-Versa-o-086_SISPAG.pdf) |
| Banco do Brasil | [PgtVer03BB — Pagamentos CNAB 240](https://www.bb.com.br/docs/portal/disem/PgtVer03BB.pdf) |
| BB (homologação) | [Validador de Leiautes](https://validaleiautes.bb.com.br/) |

## Status rápido

### v1.0.0 — Itaú (2026-06-16)

- [x] Scaffold do projeto (Composer, PHPUnit, DDD)
- [x] Layouts v086 completos (headers + segmentos A–Z)
- [x] Geração de remessa (todas as modalidades)
- [x] Leitura de retorno (113 ocorrências, tributos parseados)
- [x] Validador de layout + CLI `bin/validate-itau`
- [x] Documentação para integradores (`docs/`)
- [x] 125 testes automatizados
- [ ] PHPStan nível 8
- [ ] Tag v1.0.0 + Packagist

### v2.0.0 — Banco do Brasil (planejado)

- [ ] Refatoração multi-banco (interfaces + policies)
- [ ] Layouts PgtVer03BB (`001`, versão `030`)
- [ ] Remessa BB (paridade de modalidades, arquivo único com PIX)
- [ ] Validador interno + CLI `bin/validate-bb`
- [ ] Leitura de retorno BB
- [ ] Golden files + homologação no validador BB

Ver [bb-remittance.md](./bb-remittance.md) e fases 7–12 em [roadmap.md](./roadmap.md).
