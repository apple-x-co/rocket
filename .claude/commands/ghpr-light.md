---
allowed-tools: Bash(gh:*), Bash(git log:*), Bash(git branch:*), Bash(git diff:*), Bash(git rev-parse:*), Bash(git fetch:*), Bash(git push:*), Bash(echo:*), AskUserQuestion
description: Create a GitHub Pull Request (lightweight version)
model: haiku
---

> version: 1.1.0

## Context

- Current branch: !`git branch --show-current`
- Remote branches: !`git branch -r`
- Upstream: !`git rev-parse --abbrev-ref --symbolic-full-name @{u} 2>/dev/null`

**重要**: Context のブランチ情報はそのまま利用してよい（再取得しない）。ただし PR の**内容**の生成には、ステップ3で取得する差分のみを使用すること。

## Your task

現在のブランチから GitHub の Pull Request を作成します（軽量版）。必要最小限の分析で素早く PR を作成します。

### ステップ1: 事前チェック

1. **現在のブランチ確認**:
   - Context の `Current branch` を使用（再取得しない）
   - `main` または `master` または `develop` の場合は「デフォルトブランチからは PR を作成できません」と通知して終了

2. **リモートとの同期確認**:
   - Context の `Upstream` を使用（再取得しない）
   - 空の場合は「リモートブランチが設定されていません。`git push -u origin <branch-name>` で push してください」と通知して終了

### ステップ2: マージ先ブランチの選択

1. **利用可能なブランチ一覧を取得**:

   Context の `Remote branches`（`git branch -r` の出力）を処理対象とする（再取得しない）:
   - `origin/` プレフィックスを削除
   - `HEAD` を除外
   - 現在のブランチを除外
   - 重複を削除・ソート

2. **候補ブランチの優先順位付け**:
   - `develop`, `main`, `master` を優先的に表示
   - その他のブランチもアルファベット順で表示

3. **`AskUserQuestion` でマージ先を選択**:
   - `question`: "PR のマージ先ブランチを選択してください"
   - `header`: "Base branch"
   - `multiSelect`: `false`
   - `options`: ブランチ一覧を配列で指定
   - デフォルト選択: `develop` が存在する場合は `develop`、なければ `main` または `master`

### ステップ3: 既存 PR のチェック・差分分析（統合）

**🚨 重要制約**: 以下のコマンドの出力のみを使用して PR を生成します。

**既存PRの確認とコミット差分の取得を1回のコマンド実行にまとめる**。`<現在のブランチ名>` / `<ベースブランチ>` にはステップ1・2で確定した値をリテラル文字列として代入する（shell substitution 不可）。

```bash
echo "=== EXISTING_PR ==="
gh pr list --head <現在のブランチ名> --base <ベースブランチ> --state all --json number,title,state,url
echo "=== COMMITS ==="
git fetch --no-tags --quiet origin <ベースブランチ> && git log origin/<ベースブランチ>..HEAD --pretty=tformat:"### %h %s%n%b" --reverse
echo "=== FILES ==="
git diff origin/<ベースブランチ>..HEAD --name-status
echo "=== STAT ==="
git diff origin/<ベースブランチ>..HEAD --shortstat
```

**`gh` が認証エラーを返した場合**: 「`gh auth login` で認証してください」と通知して終了

**読み取りルール**:
- **コミット数** = `COMMITS` 区画の `### ` で始まる行数
- **変更ファイル数 / 追加・削除行数** = `STAT` 区画の `N files changed, N insertions(+), N deletions(-)`
- **変更ファイル一覧と新規/更新/削除** = `FILES` 区画の `A` / `M` / `D`
- **既存 PR** = `EXISTING_PR` 区画のうち `state` が `OPEN` または `DRAFT` のもの（`MERGED` や `CLOSED` は除外）

**差分が0コミットの場合**: 「ベースブランチとの差分がありません」と通知して終了

**既存 PR が見つかった場合の処理**:

**`AskUserQuestion` で対応方法を選択**:
- `question`: "このブランチから <ベースブランチ> への PR が既に存在します (#番号)。どうしますか?"
- `options`:
  - "既存の PR を更新する（推奨）"
  - "既存の PR のタイトル・本文を編集する"
  - "既存の PR を無視して新規 PR を作成する"
  - "既存の PR をブラウザで確認する"
  - "キャンセル"

**選択に応じた処理**:

**a) 既存の PR を更新する**:

`state` と `url` は `EXISTING_PR` 区画の取得結果から読み取る（再取得しない）。`<現在のブランチ名>` にはステップ1で取得したブランチ名をリテラル文字列として代入する（shell substitution 不可）。
```bash
# OPEN/DRAFT の場合のみ push
git push origin <現在のブランチ名>
```

**b) タイトル・本文を編集する**:
- 差分は取得済みのため、そのままステップ4（PR内容の生成）に進む
- PR内容を生成後、`gh pr edit` で更新

**c-e)**: その他の選択肢は `/ghpr` と同様に処理

### ステップ4: PR 内容の生成（1つのみ）

コミット内容とファイル一覧から、以下の形式で PR 内容を自動生成します。

**タイトルの生成ルール**:
- コミットメッセージの主題から抽出
- 1つのコミット: そのコミットの主題をベースに
- 複数コミット: 全体を要約した簡潔なタイトル
- 50文字以内を目安

**本文の生成ルール**:

```markdown
## 📝 概要
[コミットメッセージから変更の要約を1-2文で記述]

## ✨ 変更内容
- **[カテゴリ名]**: [変更の簡潔な説明]
  - `ファイル名` (新規/更新/削除)
  - `ファイル名` (新規/更新/削除)
- **[カテゴリ名]**: [変更の簡潔な説明]
  - `ファイル名` (新規/更新/削除)

## 🎯 統計
- 変更ファイル: [N]件
- コミット: [N]件
- 追加: +[N]行、削除: -[N]行
```

**カテゴリ化のルール（簡易版）**:
- ファイルパスやコミットメッセージから推測
- 主要な変更種別でグループ化（機能追加、バグ修正、リファクタリングなど）
- 2-4カテゴリ程度に集約

**ファイル名の記載ルール**:
- `git diff --name-status` の出力をそのまま使用
- プロジェクトルートからの相対パス
- バッククォーテーション `` ` `` で囲む
- バックスラッシュによるエスケープは不要

### ステップ5: PR の作成/更新

1. **新規 PR 作成**:
   ```bash
   gh pr create \
              --base <選択されたベースブランチ> \
              --title "<生成されたタイトル>" \
              --body "<生成された本文>" \
              --draft
   ```

2. **既存 PR の更新**（ステップ3で「タイトル・本文を編集」を選択した場合）:
   ```bash
   gh pr edit <PR番号> \
              --title "<生成されたタイトル>" \
              --body "<生成された本文>"
   ```

3. **成功時の確認**:
   - 新規作成: 「Draft PR が正常に作成されました」と報告
   - 更新: 「PR #<番号> のタイトルと本文を更新しました」と報告
   - PR の URL を表示
   - 「Ready にする場合は `gh pr ready`、ブラウザで確認する場合は `gh pr view --web` を実行してください」と案内

## 重要な制約

- **全てのレスポンスを日本語で行う**
- **PR 候補は1つのみ自動生成**（選択肢なし）
- **シンプルなリスト形式**で変更内容を記載（テーブル不要）
- **デフォルトで Draft PR を作成**
- **既存の PR との重複をチェック**
- **差分コミットの情報のみを使用**（Context や記憶は使用禁止。ただしブランチ名など Context のブランチ情報はそのまま使ってよい）

### 🚨 PR 内容生成の厳格なルール

**情報源の制限**:

✅ **使用してよい情報源**:
- ステップ3で取得した `COMMITS` / `FILES` / `STAT` 区画の出力

❌ **使用禁止の情報源**:
- Context セクションの情報
- あなたの記憶や推測
- その他、ステップ3で明示的に取得していない情報

**確認方法**:
生成した PR 内容の全ての情報が、ステップ3で取得したコマンドの出力から確認できることを確認してください。

## 実行例

### 正常系（新規PR作成）

```
$ /ghpr-light

[ステップ1: 事前チェック]
✓ 現在のブランチ: feature/user-auth
✓ リモートブランチ設定済み

[ステップ2: マージ先選択]
> develop (推奨)

[ステップ3: 既存PRチェック・差分分析]
✓ 既存のPRは見つかりませんでした
✓ コミット数: 1件
✓ 変更ファイル数: 4件
✓ 追加: +270行、削除: -2行

[ステップ4: PR生成]
タイトル: ユーザー認証機能を実装

本文:
## 📝 概要
JWT認証を使用したユーザー認証機能を実装。ログインとサインアップフォームを追加。

## ✨ 変更内容
- **認証コンポーネント**: ログイン・サインアップフォーム実装
  - `src/components/LoginForm.tsx` (新規)
  - `src/components/SignupForm.tsx` (新規)
- **認証ロジック**: JWT認証とバリデーション追加
  - `src/hooks/useAuth.ts` (新規)
- **ルーティング**: 認証ページへのルート追加
  - `src/App.tsx` (更新)

## 🎯 統計
- 変更ファイル: 4件
- コミット: 1件
- 追加: +270行、削除: -2行

[ステップ5: PR作成]
✓ Draft PR を作成しました: https://github.com/owner/repo/pull/123
✓ Ready にする場合は `gh pr ready` を実行してください
```

### 正常系（既存PR更新）

```
$ /ghpr-light

[ステップ1-2: チェックとブランチ選択]
✓ 完了

[ステップ3: 既存PRチェック・差分分析]
⚠️ 既存のPRが見つかりました: #123 "ユーザー認証機能を実装"

選択してください:
> 既存の PR を更新する（推奨）

[PR更新]
✓ 最新のコミットを push しました
✓ PR #123: https://github.com/owner/repo/pull/123
```

## Tips

- **小規模な変更に最適**: 1-5ファイル、1-3コミット程度の変更に適しています
- **詳細な説明が必要な場合**: `/ghpr` (詳細版) を使用してください
- **Draft から Ready への変更**: PR作成後に `gh pr ready` を実行
- **Issue とのリンク**: 本文に `Closes #123` を手動で追加可能
