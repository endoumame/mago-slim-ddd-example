---
name: repository-design
description: >
  DDDにおけるリポジトリの設計ルールとアンチパターンを提供する。集約単位の命名規則、
  CQS（Command Query Separation）に基づくメソッド設計、入出力の型制約をチェックする。
  トリガー：「リポジトリの設計をレビュー」「Repository名がおかしい」「findByIdの戻り値」
  「リポジトリがDTOを返している」「集約単位のリポジトリ」「リポジトリのアンチパターン」
  といったリポジトリ設計関連リクエストで起動。
---

# Repository Design

リポジトリは集約のI/Oに特化した責務である。

## 設計原則

### 命名規則

リポジトリ名は `集約名 + Repository` でなければならない。

### CQS（Command Query Separation）

- **Query（問い合わせ）**: 集約を返す。副作用なし。
- **Command（命令）**: 集約を受け取り、voidを返す。状態を変更する。

## アンチパターン

- findByIdの戻り値が集約でない（DTOやRecordを返す）
- ドメインロジックを含むメソッド名（leave, activate, cancel等）
- DB操作を想起するメソッド名（insert, update, select, upsert等）
- リポジトリから別のリポジトリを呼び出す
- storeがDTOを受け取る、またはIDやRecordを返す（CQS違反）

## 許可されるメソッド名

- 単体系: store, findById, delete, put, remove, add
- 複数形: storeMulti, findByIds, deleteMulti, putMulti, addMulti

## 関連スキル（併読推奨）
- `aggregate-design`: リポジトリが永続化する集約の設計ルール
- `error-handling`: リポジトリのエラー処理方式
