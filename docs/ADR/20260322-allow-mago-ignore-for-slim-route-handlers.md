# Allow @mago-ignore for Unused Parameters in Slim Route Handlers

| | |
|---|---|
| **Status** | accepted |
| **Date** | 2026-03-22 |
| **Decision-makers** | Project team |
| **Consulted** | - |
| **Informed** | All developers |

## Context and Problem Statement

mago analyzerを最大厳格レベルに引き上げた際、Slim Framework のルートハンドラで `unused-parameter` の警告（helpレベル）が発生した。Slim + PHP-DI Bridge はリフレクションを用いてパラメータ名でルート引数を解決するため、`$request` や `$response` といったパラメータ名を変更（例: `$_request`）するとランタイムで `NotEnoughParametersException` が発生する。

プロジェクトでは原則として `@mago-ignore` の使用を禁止しているが、この場合はフレームワークの制約により例外的な対応が必要となった。

## Decision Drivers

* mago analyzerの最大厳格レベルを維持したい
* フレームワーク（Slim + PHP-DI Bridge）がパラメータ名に依存するため、リネームは不可能
* `@mago-ignore` の使用は原則禁止としている
* CI を安定的に通過させる必要がある

## Considered Options

1. パラメータ名をアンダースコアプレフィックスにリネーム（`$_request`, `$_response`）
2. `@mago-ignore` で該当箇所を抑制
3. 現状維持（helpレベル警告を許容、analyzerは exit code 0 で通過）

## Decision Outcome

**Chosen option**: 「3. 現状維持（helpレベル警告を許容）」を基本とし、将来的に警告が増加した場合は「2. `@mago-ignore`」を例外的に許可する。

### Consequences

**Positive:**
* パラメータ名がフレームワークの期待と一致し、ランタイムエラーが発生しない
* mago analyzerは exit code 0 で通過するため CI に影響しない
* コードに余計なアノテーションが不要

**Negative:**
* `mago analyze` 実行時にhelpレベルの警告が出力される（6件）
* 将来的に警告がerrorレベルに昇格した場合、`@mago-ignore` の使用が必要になる

**Neutral:**
* `@mago-ignore` の原則禁止ポリシーに対する初の例外ケースとなる

### Confirmation

* `mago analyze` が exit code 0 で完了すること
* `phpunit` の全テストがパスすること（特にインテグレーションテスト）
* `unused-parameter` の警告が `src/Infrastructure/Http/Controller/TaskController.php` のSlimルートハンドラに限定されていること

## Pros and Cons of the Options

### 1. パラメータ名をアンダースコアプレフィックスにリネーム

magoの推奨する方法で未使用パラメータに `_` プレフィックスを付与する。

* Good, because mago analyzerの警告が完全に消える
* Bad, because PHP-DI の Invoker がパラメータ名で解決するため `NotEnoughParametersException` が発生する
* Bad, because ランタイムエラーにより全てのルートハンドラが動作しなくなる

### 2. `@mago-ignore` で該当箇所を抑制

各メソッドに `@mago-ignore unused-parameter` を付与して警告を抑制する。

* Good, because 警告出力がクリーンになる
* Good, because 意図的な抑制であることがコード上で明示される
* Bad, because `@mago-ignore` 原則禁止ポリシーに反する
* Bad, because コントローラのメソッドにボイラープレート的なアノテーションが増える

### 3. 現状維持（helpレベル警告を許容）

警告は出るが、exit code 0 で通過するためそのまま許容する。

* Good, because コードに変更が不要
* Good, because ランタイムの安全性が保たれる
* Good, because ポリシー違反なし
* Neutral, because 警告出力にノイズが残る

## More Information

* **根本原因**: Slim Framework + PHP-DI Bridge (`DI\Bridge\Slim\ControllerInvoker`) がリフレクションベースでパラメータ名を使って `$request`, `$response`, ルートパラメータを注入する設計
* **影響範囲**: `src/Infrastructure/Http/Controller/TaskController.php` の `get`, `update`, `delete`, `changeStatus` メソッド
* **関連設定**: `mago.toml` の `[analyzer]` セクション — `find-unused-parameters = true`
