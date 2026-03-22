---
name: parse-dont-validate
description: >-
  「Parse, don't validate」原則に基づくコードレビューと設計支援。validateパターン（チェックして結果を捨てる）
  をparseパターン（チェック結果を型で保持）に変換し、型システムで不変式を強制する設計を促進する。
  トリガー：「バリデーションを改善して」「型で保証したい」「shotgun parsingを直して」
  「不正な状態を型で防ぎたい」等の型安全性関連リクエストで起動。
---

# Parse, Don't Validate

情報を捨てるvalidationから、情報を保持するparsingへ変換する。

## 核心原則

**チェック結果を捨てずに型で保持する。**

| アプローチ | 戻り値 | 情報 | 問題 |
|-----------|--------|------|------|
| Validate | void / bool | 捨てる | 再チェック必要、型が保証しない |
| Parse | 型付き値 | 保持 | 一度のチェックで済む、型が保証 |

## アンチパターン検出

```
❌ validate*() → void
❌ check*() → bool
❌ is*() → bool（分岐後に同じ値を使う場合）
❌ "should never happen" コメント
```

## 変換パターン

1. NonEmpty変換
2. 重複キー検出 → Map変換
3. Smart Constructor

## 適用指針

### 推奨
- システム境界での入力処理
- 複雑な不変式を持つドメインモデル
- Option が頻出する箇所

### 過剰適用を避ける
- 既存APIとの互換性が必要な場合
- パフォーマンスクリティカルなホットパス

## 関連スキル（併読推奨）
- `domain-primitives-and-always-valid`: スマートコンストラクタによるドメインプリミティブの設計
- `domain-building-blocks`: 値オブジェクトの設計
