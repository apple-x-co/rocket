<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

use Rocket\Slack\BlockKit\Element\ElementInterface;

/**
 * rich_text_section / rich_text_quote / rich_text_preformatted の中に置ける葉要素
 * （text, link, emoji, user, usergroup, channel, team, date, broadcast, color）
 */
interface RichTextElementInterface extends ElementInterface
{
}
