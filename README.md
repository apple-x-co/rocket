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
- `Block\Card`（card）
- `Block\Carousel`（carousel）
- `Block\Container`（container）
- `Block\ContextActions`（context_actions）
- `Block\TaskCard`（task_card）
- `Block\Plan`（plan）

### Plan

```php
use Rocket\Slack\BlockKit\Block\Plan;
use Rocket\Slack\BlockKit\Block\RichText;
use Rocket\Slack\BlockKit\Element\Plan\Task;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\RichText\Text;

$plan = (new Plan('Thinking completed'))
    ->addTask(new Task('call_001', 'Fetched user profile information', Task::STATUS_IN_PROGRESS))
    ->addTask(new Task('call_002', 'Checked user permissions', Task::STATUS_PENDING))
    ->addTask(
        (new Task('call_003', 'Generated comprehensive user report', Task::STATUS_COMPLETE))
            ->setOutput(
                (new RichText())->addElement(
                    (new RichTextSection())->addElement(new Text('15 data points compiled'))
                )
            )
    );

$message->addBlock($plan);
```

`addTask()` に渡す `Element\Plan\Task` は `Block\TaskCard` とは別の値オブジェクトです。`status` は `Task::STATUS_IN_PROGRESS` / `STATUS_PENDING` / `STATUS_COMPLETE` のいずれかで、Task card ブロックの `error` は存在しません。`title` は素の文字列、`details` / `output` には `Block\RichText` を指定できます。`tasks` は最大 50 個、各 `task_id` は Plan 内で一意である必要があります。

> ⚠️ **既知の制約**: `Block\Plan` と `Block\TaskCard` は同じメッセージ内に同時に含めることはできません。両方を含めて `chat.postMessage` すると `invalid_blocks`（`"Plan block and task blocks are mutually exclusive"`）エラーになります（公式ドキュメントには明記されていませんが、実機検証で確認済みです）。

### TaskCard

```php
use Rocket\Slack\BlockKit\Block\RichText;
use Rocket\Slack\BlockKit\Block\TaskCard;
use Rocket\Slack\BlockKit\Element\Card\SlackIcon;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\RichText\Text;
use Rocket\Slack\BlockKit\Element\TaskCard\UrlSource;

$output = (new RichText())->addElement(
    (new RichTextSection())->addElement(new Text('Found weather data for Chicago from 2 sources'))
);

$taskCard = (new TaskCard('task_1', 'Fetching weather data', TaskCard::STATUS_IN_PROGRESS))
    ->setIcon(new SlackIcon('rocket'))
    ->setOutput($output)
    ->addSource(new UrlSource('https://weather.com/', 'weather.com'))
    ->addSource(new UrlSource('https://www.accuweather.com/', 'accuweather.com'));

$message->addBlock($taskCard);
```

`status` は `TaskCard::STATUS_IN_PROGRESS` / `STATUS_COMPLETE` / `STATUS_ERROR` のいずれかを指定します。`title` は（`Section` などと違い）`PlainText` オブジェクトではなく素の文字列です。`details` / `output` には `Block\RichText` を、`icon` には Card ブロックと共通の `Element\Card\SlackIcon` を再利用でき、`addSource()` で参照元 URL（`UrlSource`）を追加できます。

### ContextActions

```php
use Rocket\Slack\BlockKit\Block\ContextActions;
use Rocket\Slack\BlockKit\Element\ContextActions\FeedbackButton;
use Rocket\Slack\BlockKit\Element\ContextActions\FeedbackButtons;
use Rocket\Slack\BlockKit\Element\ContextActions\IconButton;
use Rocket\Slack\BlockKit\Element\PlainText;

$contextActions = (new ContextActions())
    ->addElement(
        new FeedbackButtons(
            new FeedbackButton(new PlainText(':+1:', true), 'positive_feedback'),
            new FeedbackButton(new PlainText(':-1:', true), 'negative_feedback'),
            'feedback_buttons_1'
        )
    )
    ->addElement(
        (new IconButton(IconButton::ICON_TRASH, new PlainText('Delete')))
            ->setActionId('delete_button_1')
            ->setValue('delete_item')
    );

$message->addBlock($contextActions);
```

`addElement()` には `FeedbackButtons`（👍/👎 のような 2 択フィードバック）と `IconButton`（現状 `IconButton::ICON_TRASH` のみ対応）を最大 5 個まで追加できます。いずれもクリック時は Slack アプリの Interactivity 用エンドポイントへ通知が送られる仕組みで、rocket 自体はそれを受け取るサーバーを持たないため、実際に応答処理をしたい場合は別途 Slack アプリ側の実装が必要です。

### Container

```php
use Rocket\Slack\BlockKit\Block\Container;
use Rocket\Slack\BlockKit\Block\Context;
use Rocket\Slack\BlockKit\Block\Divider;
use Rocket\Slack\BlockKit\Block\Section;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;

$container = (new Container())
    ->setTitle(new PlainText('Bulk update: 2 records selected'))
    ->setSubtitle(new PlainText('Review changes before confirming'))
    ->setCollapsible(true)
    ->addChildBlock((new Section())->setText(new Mrkdwn('*DCW-1024*' . PHP_EOL . 'Status: Open → Closed')))
    ->addChildBlock(new Divider())
    ->addChildBlock((new Context())->addElement(new Mrkdwn(':white_check_mark: 1 record will be updated')));

$message->addBlock($container);
```

`child_blocks` には他のブロックを最大 10 個まで追加できます（`addChildBlock()`）。ただし Slack がサポートする子ブロックの type は `actions` / `context` / `divider` / `file` / `header` / `image` / `input` / `rich_text` / `section` / `table` / `video` に限られ、`markdown` / `data_table` / `data_visualization` / `card` は非対応です。`title`（`PlainText`）の代わりに `setRichTextTitle()`（`Block\RichText`）でリッチテキストのタイトルも指定できます（両方指定時は `rich_text_title` が優先）。`is_collapsible` を `true` にすると `setDefaultCollapsed()` で初期状態を折りたたみにできます。

### Card

```php
use Rocket\Slack\BlockKit\Block\Card;
use Rocket\Slack\BlockKit\Element\Button;
use Rocket\Slack\BlockKit\Element\Card\SlackIcon;
use Rocket\Slack\BlockKit\Element\Image;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;

$card = (new Card())
    ->setIcon(new Image('https://picsum.photos/36/36', 'Icon'))
    ->setTitle(new Mrkdwn('Lumon Industries'))
    ->setSubtitle(new Mrkdwn('Committed to work-life balance'))
    ->setHeroImage(new Image('https://picsum.photos/400/300', 'Sample hero image'))
    ->setBody(new Mrkdwn('Please enjoy each card equally.'))
    ->addAction(new Button(new PlainText('Action Button'), 'button_action'));

$message->addBlock($card);
```

`icon` は事前定義済みアイコンを使う `SlackIcon`（`new SlackIcon('star-filled')` など）で代替可能です。ただし `icon` と `slack_icon` は同じ位置に描画されるため排他利用です。`title` / `subtitle` / `body` / `subtext` には `PlainText` / `Mrkdwn` が使用でき、`addAction()` には `Button` を最大 3 個まで追加できます。

`Button` は `action_id` のみ（`setUrl()` 未指定）だと、クリック時に Slack アプリの Interactivity 用エンドポイントへリクエストが送られるだけで、その受け口（サーバー）がない場合は見た目上何も起こりません。クリックしてリンクを開かせたい場合は `->setUrl('https://example.com/')` を併用してください。

### Carousel

```php
use Rocket\Slack\BlockKit\Block\Card;
use Rocket\Slack\BlockKit\Block\Carousel;
use Rocket\Slack\BlockKit\Element\Mrkdwn;

$carousel = (new Carousel())
    ->addElement((new Card())->setTitle(new Mrkdwn('MDR'))->setSubtitle(new Mrkdwn('Refining data files')))
    ->addElement((new Card())->setTitle(new Mrkdwn('O&D'))->setSubtitle(new Mrkdwn('Optics and design')));

$message->addBlock($carousel);
```

`addElement()` には `Block\Card` のみを最大 10 個（最低 1 個）まで追加できます。カード自体の組み立て方は上記 Card セクションと同じです。

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
