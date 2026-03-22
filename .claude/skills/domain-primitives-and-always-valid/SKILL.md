---
name: domain-primitives-and-always-valid
description: >-
  プリミティブ型を信頼せず、ドメイン固有の型で不変条件を強制する設計ガイド。
  Secure by DesignのDomain Primitivesパターンと、Always-Valid Domain Modelの原則を
  組み合わせた型駆動設計を支援する。
  トリガー：「値オブジェクトの型を作りたい」「プリミティブ型をラップしたい」
  「スマートコンストラクタ」「Always-Valid」「ドメインプリミティブ」等で起動。
---

# Domain Primitives & Always-Valid Domain Model

プリミティブ型を信頼せず、ドメイン固有の型で不変条件を強制する。

## 核心原則

### Domain Primitives（Secure by Design）

プリミティブ型をそのまま使わず、ドメイン固有の最小単位の型でラップする。

| 特性 | 説明 |
|------|------|
| 構築時検証 | 無効な値でインスタンスを作成できない |
| 不変（Immutable） | 一度作成されたら変更できない |
| 自己完結 | 他のエンティティへの参照を持たない |
| ドメイン操作の集約 | その型に関連する操作をカプセル化 |
| 引数の取り違え防止 | 同じプリミティブ型でも異なるドメイン型として区別 |

### Always-Valid Domain Model

ドメインモデルは常に有効な状態にあることを型システムで保証する。

```
オブジェクトが存在する = そのオブジェクトは有効である
```

## 設計パターン

1. **Smart Constructor**: コンストラクタをprivateにし、ファクトリメソッドで検証を強制
2. **複合値のカプセル化**: 関連する値を単一の型でまとめる
3. **範囲制約型**: 有効な範囲のみを表現する型
4. **NonEmpty型**: 空でないことを型で保証

## 判断フロー

```
プリミティブ型を使おうとしている
    ↓
この値にドメイン固有の制約があるか？
    ├─ Yes → Domain Primitiveを作成
    │    ├─ フォーマット制約 → Smart Constructor + パーサー
    │    ├─ 範囲制約 → 境界チェック付きコンストラクタ
    │    ├─ 複合値 → 関連する値をまとめた型
    │    └─ 非空制約 → NonEmpty<T>
    └─ No → プリミティブ型のままでOK（稀）
```

## 適用指針

### 推奨
- ID型（UserId, OrderId等）
- 連絡先情報（Email, PhoneNumber等）
- 金融情報（Money, Currency等）
- 測定値（Temperature, Distance等）
- 非空コレクション

### 過剰適用を避ける
- 一時的なローカル変数
- プライベートな内部実装の詳細

## 関連スキル（併読推奨）
- `parse-dont-validate`: 型レベルで不変式を保証する設計哲学
- `domain-building-blocks`: ドメインプリミティブが構成する値オブジェクトの設計
