---
name: ddd-module-pattern
description: >-
  DDDにおけるモジュールパッケージング戦略。ドメイン層では技術的な分類（entities/、value-objects/等）
  を避け、ビジネス概念に基づくパッケージ命名を推奨する。
  トリガー：「DDDのモジュール構成」「ドメイン層のパッケージ」「技術駆動パッケージを直したい」
  「entities/ディレクトリをなくしたい」等で起動。
---

# DDDモジュールパターン

> Eric Evans: 「モジュールはドメインの概念を反映すべきであり、技術的な関心事ではない」

## 核心原則

ドメイン層で技術的な分類（entities/, value-objects/, services/等）を避け、
ビジネス概念に基づくパッケージ命名を使う。

## 避けるべきドメイン層のパッケージ名

entities, value-objects, services, impl, dto

## 推奨される命名

order, customer, pricing 等、業務領域を直接反映した名称

## リファクタリングプロセス

1. ドメイン概念の抽出
2. パッケージ境界設定
3. 型の移動と参照更新
4. 依存方向の確認

**注**: インフラストラクチャ層では技術駆動パッケージングも許容される。

## 関連スキル（併読推奨）
- `package-design`: 一般的なパッケージ設計原則
- `clean-architecture`: 各層の配置ガイド
