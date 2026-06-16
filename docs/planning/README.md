# Planejamento — cnab-sispag

Documentação interna do projeto. Para integradores, use [docs/README.md](../README.md).

## Índice

| Documento | Conteúdo |
|---|---|
| [PLAN.md](./PLAN.md) | Visão geral, escopo v1.0, entregáveis |
| [architecture.md](./architecture.md) | DDD, camadas, API pública |
| [conventions.md](./conventions.md) | Inglês no código, português nas mensagens |
| [segments.md](./segments.md) | Inventário de segmentos e regras de combinação |
| [roadmap.md](./roadmap.md) | Fases, estimativas e status |
| [checklist.md](./checklist.md) | Checklist detalhado tarefa a tarefa |
| [file-map.md](./file-map.md) | Mapa de arquivos do repositório |

## Referência externa

- [Manual SISPAG Itaú CNAB 240 v086](https://www.itau.com.br/media/dam/m/3b9688b3979b4016/original/Layout-de-Arquivos_CNAB-Versa-o-086_SISPAG.pdf)

## Status rápido (v1.0.0 — 2026-06-16)

- [x] Scaffold do projeto (Composer, PHPUnit, DDD)
- [x] Layouts v086 completos (headers + segmentos A–Z)
- [x] Geração de remessa (todas as modalidades)
- [x] Leitura de retorno (113 ocorrências, tributos parseados)
- [x] Validador de layout + CLI `bin/validate-itau`
- [x] Documentação para integradores (`docs/`)
- [x] 125 testes automatizados
- [ ] PHPStan nível 8
- [ ] Tag v1.0.0 + Packagist
