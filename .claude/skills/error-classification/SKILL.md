---
name: error-classification
description: >
  ソフトウェアにおける非正常状態（Error, Defect, Fault, Failure）の分類と定義を提供する。
  JIS規格・JSTQB・Bertrand Meyer等の複数の標準に基づき、各概念の違いを明確化し、
  適切なエラー設計・障害対策の判断を支援する。
  トリガー：「ErrorとFaultの違い」「DefectとBugの関係」「障害と故障の区別」
  「エラーの分類」「非正常状態の設計」「エラー用語の定義」等で起動。
---

# Error, Defect, Fault, Failure 分類ガイド

ソフトウェアの非正常状態を正確に分類し、適切な対処戦略を導く。

## 分類と対処戦略

| 分類 | 対処戦略 | 実装パターン |
|------|---------|-------------|
| **Error** | 呼び出し元で対処 | Either/Result型、バリデーション |
| **Defect** | 即座に検出・停止 | アサーション（require/assert）、事前条件チェック |
| **Fault** | 隔離と回復 | Error Kernel、Let it crash、サーキットブレーカー |
| **Failure** | 予防と冗長化 | フェイルオーバー、冗長構成、監視・アラート |

## 因果関係

```
Error（エラー） → 想定内。呼び出し元で対処可能
Defect（欠陥） → 本来あってはならない。修正されなければ Fault を引き起こす
Fault（障害）  → 異常状態。継続すると Failure に至る
Failure（故障） → 機能喪失。ユーザーに影響
```

## レビューチェックリスト

- [ ] Error/Defect/Fault/Failureが適切に区別されているか
- [ ] 想定内のErrorをResult型で表現しているか
- [ ] Defectをアサーションで検出しているか
- [ ] ErrorとDefectを混同して同じハンドリングをしていないか
- [ ] 例外を「何でもcatch」して、Defectを握り潰していないか

## 関連スキル（併読推奨）
- `error-handling`: 分類に基づくエラー処理の実装パターン
- `aggregate-design`: 集約操作における不変条件違反とドメインエラーの区別
