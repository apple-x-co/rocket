# rocket

rsync によるファイル同期・Git pull・Slack 通知を組み合わせた、PHP 製のデプロイ CLI ツールです。

## ✨ Features

- **rsync によるファイル同期** — dry run / 確認付き / 強制の 3 モードに対応
- **Git pull** — リモートに更新がある場合のみ pull を実行
- **Slack 通知** — デプロイ完了時に差分ログ付きで自動通知
- **設定ファイルテンプレート** — plain / CakePHP 3 / EC-CUBE 4 / WordPress に対応
- **単一 phar ファイル** — インストール不要で即使用可能

## 📋 Requirements

- PHP 5.4 / 7.x / 8.x
- Extensions: `ext-curl`, `ext-json`, `ext-posix`, `ext-zip`

## 📦 Installation

```bash
# ダウンロード
wget https://github.com/apple-x-co/rocket/releases/latest/download/rocket.phar
chmod u+x rocket.phar

# 最新版へのアップグレード
./rocket.phar --upgrade
```

## 🚀 Quick Start

典型的なセットアップから初回デプロイまでの流れです。

```bash
# 1. 設定ファイルを生成（テンプレートを選択）
./rocket.phar --init > ./rocket.json
#   テンプレート: plain（デフォルト）, cakephp3, eccube4, wordpress
#   例: ./rocket.phar --init=wordpress > ./rocket.json

# 2. 設定ファイルを検証
./rocket.phar --config ./rocket.json --verify

# 3. Slack 通知をテスト
./rocket.phar --config ./rocket.json --notify-test

# 4. ドライランで差分を確認
./rocket.phar --config ./rocket.json --sync dry

# 5. Git pull + デプロイ（確認あり）
./rocket.phar --config ./rocket.json --git pull --sync confirm
```

デプロイ後、ファイルに変更があった場合は Slack に以下の内容が自動通知されます。

- デプロイ実行ユーザー・ホスト名・URL
- Git pull ログ（実行した場合）
- rsync による変更ファイル一覧

## 🛠 Commands

### デプロイ（rsync）

`--sync` の動作モード：

| モード    | 動作                                   |
|-----------|----------------------------------------|
| `dry`     | ドライランのみ（実際の転送は行わない） |
| `confirm` | ドライランを表示し、`y` 入力で本実行   |
| `force`   | 確認なしで即実行                       |

```bash
# ドライラン
./rocket.phar -c ./rocket.json -s dry

# Git pull してから確認付きデプロイ
./rocket.phar -c ./rocket.json -g pull -s confirm

# 強制デプロイのみ
./rocket.phar -c ./rocket.json -s force
```

### Slack 通知

```bash
# 任意のテキストを通知
echo "HELLO WORLD" | ./rocket.phar -c ./rocket.json --notify

# パイプで複数行も送信可能
cat deploy.log | ./rocket.phar -c ./rocket.json --notify
```

### その他

```bash
# バージョン情報を表示
./rocket.phar --info

# ヘルプを表示
./rocket.phar --help

# 設定ファイルを検証
./rocket.phar -c ./rocket.json --verify
```

## ⚙️ Options

| Option                                         | Short | Description                             |
|------------------------------------------------|-------|-----------------------------------------|
| `--config <file>`                              | `-c`  | 設定ファイルのパス（JSON）              |
| `--git [pull]`                                 | `-g`  | Git 操作                                |
| `--sync [dry\|confirm\|force]`                 | `-s`  | rsync 操作                              |
| `--notify`                                     | `-n`  | Slack 通知（stdin から読み込み）        |
| `--notify-test`                                |       | Slack 通知テスト                        |
| `--verify`                                     | `-v`  | 設定ファイルの検証                      |
| `--init [plain\|cakephp3\|eccube4\|wordpress]` | `-i`  | 設定ファイルテンプレートを出力          |
| `--upgrade`                                    | `-u`  | 最新バージョンをダウンロード            |
| `--unzip <path>`                               |       | アップグレード時に使用する unzip のパス |
| `--ssl [TLSv1_0\|TLSv1_1\|TLSv1_2\|TLSv1_3]`   |       | SSL バージョンを指定                    |
| `--info`                                       |       | バージョン情報を表示                    |
| `--help`                                       | `-h`  | ヘルプを表示                            |
| `--no-color`                                   |       | カラー出力を無効化                      |
| `--debug`                                      |       | 実行コマンドをデバッグ表示              |

## 📝 Configuration

`--init` で生成される設定ファイルのリファレンスです。

```json
{
  "version": "1.2",
  "user": "centos-user",
  "url": "https://example.com/",
  "slack": {
    "channel": "channel-name",
    "username": "project-name",
    "chatPostMessageUrl": "https://slack.com/api/chat.postMessage",
    "appOauthToken": "xxx",
    "icon": ":tada:"
  },
  "source": {
    "directory": "/home/sample/source/"
  },
  "destinations": [
    {
      "from": "/home/sample/source/htdocs/",
      "to": "/var/www/vhosts/example.com/htdocs/",
      "excludes": [
        ".gitkeep",
        ".gitignore",
        "healthcheck.txt"
      ],
      "scripts": [
        {
          "path": "/path/to/script",
          "option": "argument"
        }
      ]
    }
  ],
  "rsync": {
    "path": "/usr/bin/rsync",
    "option": "--recursive --links --checksum --verbose --human-readable --delete"
  },
  "git": {
    "path": "/usr/bin/git"
  }
}
```

| Key                       | Required | Description                                        |
|---------------------------|----------|----------------------------------------------------|
| `user`                    | ✓       | デプロイを許可するシステムユーザー名               |
| `url`                     | ✓       | デプロイ先 URL（Slack 通知に表示）                 |
| `slack.channel`           | ✓       | Slack チャンネル名                                 |
| `slack.username`          | ✓       | Slack 投稿ユーザー名                               |
| `slack.incomingWebhook`   | ✓       | Slack Incoming Webhook URL                         |
| `slack.icon`              |          | Slack アイコン（絵文字、デフォルト: `:sparkles:`） |
| `source.directory`        |          | Git リポジトリのディレクトリパス                   |
| `destinations[].from`     | ✓       | rsync の転送元ディレクトリ                         |
| `destinations[].to`       | ✓       | rsync の転送先ディレクトリ                         |
| `destinations[].excludes` |          | rsync で除外するパス                               |
| `destinations[].scripts`  |          | 同期後に実行するスクリプト                         |
| `rsync.path`              |          | rsync のパス（デフォルト: `/usr/bin/rsync`）       |
| `rsync.option`            |          | rsync オプション                                   |
| `git.path`                |          | git のパス（デフォルト: `/usr/bin/git`）           |

## 🧱 Slack Block Kit

`Rocket\Slack\BlockKit` 名前空間に、[Slack Block Kit](https://docs.slack.dev/block-kit) のブロックを組み立てる PHP クラス群を同梱しています（PHP 5.4 互換）。

対応ブロック:

- `Block\Section` / `Block\Divider` / `Block\Header` / `Block\Image` / `Block\Context` / `Block\Markdown`
- `Block\RichText`（rich_text）
- `Block\Table`（table）
- `Block\DataTable`（data_table）
- `Block\DataVisualization`（data_visualization）

### Table

```php
use Rocket\Slack\BlockKit\Block\Table;
use Rocket\Slack\BlockKit\Element\DataTable\RawNumber;
use Rocket\Slack\BlockKit\Element\DataTable\RawText;
use Rocket\Slack\BlockKit\Element\Table\ColumnSetting;

$table = (new Table())
    ->addRow([new RawText('File'), new RawText('Size')])
    ->addRow([new RawText('index.php'), new RawNumber(1024, '1,024 B')])
    ->addColumnSetting(new ColumnSetting(ColumnSetting::ALIGN_LEFT, true))
    ->addColumnSetting(new ColumnSetting(ColumnSetting::ALIGN_RIGHT));

$message->addBlock($table);
```

セルには `RawText` / `RawNumber` に加えて `Block\RichText` も使用できます。`data_table` と異なりページングやキャプションはなく、列ごとの表示設定（`align` / `is_wrapped`）を `ColumnSetting` で指定できます。

> ⚠️ **既知の問題**: 2026-09 時点で確認したところ、`table` ブロックの `raw_number` セルは Slack 上で値が空白表示になる事例を確認しています（`blocks.validate` API ではペイロードは有効と判定されるため、Slack 側の描画の問題と考えられます）。同じ形式の `raw_number` セルでも `data_table` ブロックでは正常に表示されます。数値を確実に表示したい場合は、`raw_number` の代わりに `RawText` で文字列として渡すことを検討してください。

### DataTable

```php
use Rocket\Slack\BlockKit\Block\DataTable;
use Rocket\Slack\BlockKit\Element\DataTable\RawNumber;
use Rocket\Slack\BlockKit\Element\DataTable\RawText;

$table = (new DataTable('Deploy History'))
    ->addRow([new RawText('Date'), new RawText('User'), new RawText('Duration (sec)')])
    ->addRow([new RawText('2026/09/05 10:00:00'), new RawText('sano'), new RawNumber(12, '12')]);

$message->addBlock($table);
```

セルには `RawText` / `RawNumber` に加えて `Block\RichText` も使用できます（1 行目のヘッダー行を除く）。

### DataVisualization

```php
use Rocket\Slack\BlockKit\Block\DataVisualization;
use Rocket\Slack\BlockKit\Element\Chart\AxisConfig;
use Rocket\Slack\BlockKit\Element\Chart\DataPoint;
use Rocket\Slack\BlockKit\Element\Chart\LineChart;
use Rocket\Slack\BlockKit\Element\Chart\Series;

$chart = (new LineChart(new AxisConfig(['Mon', 'Tue', 'Wed'], 'Day', 'Seconds')))
    ->addSeries(
        (new Series('Duration'))
            ->addDataPoint(new DataPoint('Mon', 12))
            ->addDataPoint(new DataPoint('Tue', 9))
            ->addDataPoint(new DataPoint('Wed', 15))
    );

$message->addBlock(new DataVisualization('Deploy Duration', $chart));
```

チャートの種類は `PieChart` / `BarChart` / `AreaChart` / `LineChart` の 4 種類です。1 メッセージに含められる `DataVisualization` ブロックは最大 2 個までという Slack 側の制約があります。
