---
name: error-handling
description: >
  エラーハンドリングのベストプラクティス。回復可能性を基準にしたエラー分類と、
  Either/Result型による型安全なエラー処理パターンを提供する。
  トリガー：「エラーハンドリング」「Either型」「Result型」「例外処理」
  「回復可能なエラー」「エラー設計」等で起動。
---

# Error Handling

回復可能性を基準にエラーを分類し、適切な処理パターンを選択する。

## 核心原則

- **回復可能なエラー**: Either/Result型で表現し、呼び出し元に判断を委ねる
- **回復不能なエラー**: 例外またはpanicで即座に停止

## 判断フロー

「このエラーは回復可能か？」
- Yes → Result/Either型を使用
- No → 例外/panicで即座に停止

## 関連スキル（併読推奨）
- `error-classification`: エラーの概念的分類（Error/Defect/Fault/Failure）
- `domain-building-blocks`: 値オブジェクト構築時のエラー分類
