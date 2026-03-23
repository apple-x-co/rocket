---
allowed-tools: Bash(git add:*), Bash(git status:*), Bash(git commit:*), Bash(git log:*), Bash(git branch:*), Bash(git diff:*), AskUserQuestion
description: Create a git commit
---

## Context

- Current git status: !`git status`
- Current git diff (staged and unstaged changes): !`git diff HEAD`
- Current branch: !`git branch --show-current`
- Recent commits: !`git log --oneline -10`
- Branch name for a commit message: !`git branch --show-current`

## Your task

変更内容を分析し、**必ず3つ**のコミットメッセージ候補を生成してユーザーに選択させます。

### ステップ1: 変更の分析

`git status` と `git diff HEAD` の出力から以下を把握：
- 変更の種類（新機能/バグ修正/リファクタリング/ドキュメント/その他）
- 影響を受けるファイルとその役割
- 変更の主要な目的

### ステップ2: 3つの候補を生成

**必ず以下の3パターン**で候補を作成：

1. **詳細版（推奨）**: 変更内容の具体的な詳細を含む
   - 形式: `<type>: <主要な変更> <詳細や補足情報> <branch>`
   - 例:
     ```text
     feat: ユーザー認証機能を追加し JWT トークンベースの認証を実装 issues/#123
     ```

2. **標準版**: 変更の要点を簡潔に表現
   - 形式: `<type>: <変更の要約> <branch>`
   - 例:
   ```text
   feat: ユーザー認証機能を追加 issues/#123
   ```

3. **簡潔版**: 最小限の情報で変更を表現
   - 形式: `<type>: <変更内容> <branch>`
   - 例:
   ```text
   feat: 認証機能追加 issues/#123
   ```

**フォーマット規則**（全候補に適用）:
- Conventional Commits 形式を使用（`feat`/`fix`/`docs`/`refactor`/`chore`/`test`/`style`/`perf` 等）
- プレフィックス（`<type>:`）は英語
- メッセージ本文は日本語
- 1行の長さは72文字以内を推奨（ブランチ名を含む）
- 行末に必ずブランチ名（`main`/`develop`/`issues/#123`/`feat/#123`/`hotfix/#123` 等）を含める（フォーマット: `<branch>` の値をそのまま使用）
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

1. 変更がない場合は「コミット可能な変更がありません」と通知して終了
2. 未ステージの変更がある場合は `git add` で全てステージング
3. 選択されたメッセージで `git commit -m "<選択されたメッセージ>"` を実行
4. コミット失敗時はエラー内容をユーザーに報告
5. `git status` でコミット成功を確認し、結果を日本語で報告

## 重要な制約

- **全てのレスポンスを日本語で行う**（変更の分析、候補の説明、コミット後の状態報告など、すべて日本語で出力）
- **必ず3つの候補を生成**（それ以上でもそれ以下でもない）
- **Claude co-authorship フッターは追加しない**
- **コミットメッセージは必ず1行のみ**（本文や Co-Authored-By は不要）
- **詳細版を1番目（推奨）に配置**する
