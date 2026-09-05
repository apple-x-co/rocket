---
allowed-tools: Bash(git status:*), Bash(git commit:*), Bash(git log:*), Bash(git branch:*), Bash(git diff:*), AskUserQuestion
description: Create a git commit
model: haiku
---

> version: 1.1.0

## Context

- Current branch: !`git branch --show-current`
- Git status: !`git status --short`
- Staged changes: !`git diff --cached --patch-with-stat -U1`
- Recent commits: !`git log --oneline -10`

## Your task

変更内容を分析し、**必ず3つ**のコミットメッセージ候補を生成してユーザーに選択させます。

### ステップ1: ステージング状態の確認と分析

`Git status`（`git status --short`）の1列目（index列）が空白でない行の有無でステージング状態を判定する：

**ステージ済みが0件**
- 「ステージされたファイルがありません。`git add` でファイルをステージしてからコミットしてください。」と通知して処理を終了する

**ステージ済みが1件以上**
- `Staged changes`（`git diff --cached`）のみを分析対象とする
- 未ステージの変更・未追跡ファイルはコミット対象に含めない（追加の `git add` は行わない）

分析対象から以下を把握：
- 変更の種類（新機能/バグ修正/リファクタリング/ドキュメント/その他）
- 影響を受けるファイルとその役割
- 変更の主要な目的

### ステップ2: 3つの候補を生成

**必ず以下の3パターン**で候補を作成：

1. **詳細版（推奨）**: 変更内容の具体的な詳細を含む
   - 形式: `<type>: <主要な変更> <詳細や補足情報> <branch>`
   - 例: `feat: ユーザー認証機能を追加し JWT トークンベースの認証を実装 issues/#123`

2. **標準版**: 変更の要点を簡潔に表現
   - 形式: `<type>: <変更の要約> <branch>`
   - 例: `feat: ユーザー認証機能を追加 issues/#123`

3. **簡潔版**: 最小限の情報で変更を表現
   - 形式: `<type>: <変更内容> <branch>`
   - 例: `feat: 認証機能追加 issues/#123`

**フォーマット規則**（全候補に適用）:
- Conventional Commits 形式を使用（`feat`/`fix`/`docs`/`refactor`/`chore`/`test`/`style`/`perf` 等）
- プレフィックス（`<type>:`）は英語
- メッセージ本文は日本語
- 1行の長さは72文字以内を推奨（ブランチ名を含む）
- 行末に必ずブランチ名（`main`/`develop`/`issues/#123`/`feat/#123`/`hotfix/#123` 等、`Current branch` の値をそのまま使用）を含める
- ファイル名やコマンドはバッククォーテーション `` ` `` で囲む
- 英語と日本語の間に半角スペースを入れる
- 1行で完結（本文や追加行は作成しない）

### ステップ3: ユーザーに選択させる

`AskUserQuestion` ツールで以下のように提示：
- `question`: "コミットメッセージを選択してください"
- `header`: "Commit msg"
- `multiSelect`: `false`
- `options`: 3つの候補を配列で指定
  - 各 `label` には生成したコミットメッセージ全文を設定
  - 各 `description` には候補の特徴や意図を説明（「詳細版」「標準版」「簡潔版」等）

### ステップ4: コミット実行

1. `git add` は行わず、ステージ済みのファイルのみで `git commit -m "<選択されたメッセージ>"` を実行する
2. コミット成功時は `git commit` 自身の出力（ブランチ名・短縮SHA・変更ファイル数など）から結果を読み取り、日本語で報告する（確認用の追加コマンドは実行しない）
3. コミット失敗時はエラー内容をそのまま日本語で報告する

## 重要な制約

- **全てのレスポンスを日本語で行う**（変更の分析、候補の説明、コミット後の状態報告など、すべて日本語で出力）
- **必ず3つの候補を生成**（それ以上でもそれ以下でもない）
- **Claude co-authorship フッターは追加しない**
- **コミットメッセージは必ず1行のみ**（本文や Co-Authored-By は不要）
- **詳細版を1番目（推奨）に配置**する
- **コミット対象はステージ済みのファイルのみ**（`git add` は実行しない）
- **報告は `git commit` の出力から行い、確認用の追加コマンドは実行しない**
