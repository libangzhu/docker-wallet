<?php
namespace common\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class Breadcrumbs
 * @package common\widgets
 */
class Breadcrumbs extends \yii\widgets\Breadcrumbs
{
	public $active = ['id' => 10, 'pid' => 0, 'title' => '首页'];

	public $tag = 'li';

	public $itemTemplate = "<li class='menu-dropdown classic-menu-dropdown '>{link}</li>";

	public $activeItemTemplate = "<li class='menu-dropdown classic-menu-dropdown active'>{link}</li>";

	/**
	 * 下拉菜单模板 level 2
	 * @var string
	 */
	public $itemTemplate2 = "<li class='dropdown-submenu '>{link}</li>";

	public $activeItemTemplate2 = "<li class='dropdown-submenu active'>{link}</li>";

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		if (empty($this->links)) {
			return;
		}
		$links = [];
		if ($this->homeLink === null) {
			$links[] = $this->renderItem([
				'label' => Yii::t('yii', 'Home'),
				'url' => Yii::$app->homeUrl,
			], $this->itemTemplate);
		} elseif ($this->homeLink !== false) {
			$links[] = $this->renderItem($this->homeLink, $this->itemTemplate);
		}

		foreach ($this->links as $link) {
			$add1 = '';
			if (isset($link['notes'])) {
				$add1 .= "<ul class='dropdown-menu pull-left'>";
				foreach ($link['notes'] as $level1) {

					$add2 = '';
					if (isset($level1['notes'])) {
						$itemTemplate = $this->itemTemplate2;
						$activeTemplate = $this->activeItemTemplate2;
						$add2 .= "<ul class='dropdown-menu pull-left'>";
						foreach ($level1['notes'] as $level2) {

							$add2 .= $this->renderItem(
								$level2,
								isset($level2['id']) && $level2['id'] == $this->active['id'] ?
									$this->itemTemplate : $this->activeItemTemplate
							);
						}
						$add2 .= "</ul>";
					} else {
						$itemTemplate = $this->itemTemplate;
						$activeTemplate = $this->activeItemTemplate;
					}

					$add1 .= $this->renderItem(
						$level1,
						isset($level1['id']) && $level1['id'] == $this->active['id'] ?
							$activeTemplate : $itemTemplate,
						$add2
					);
				}
				$add1 .= "</ul>";
			}

			if (!is_array($link)) {
				$link = ['label' => $link];
			}

			if ( $link['id'] == $this->active['pid'] || ($link['id'] == $this->active['id'] && 0 == $this->active['pid']) ) {
				$link['label'] = $this->active['title'];
				$that = $this->renderItem(
					$link,
					$this->activeItemTemplate,
					$add1
				);
			} else {
				$that = $this->renderItem(
					$link,
					$this->itemTemplate,
					$add1
				);
			}

			$links[] = $that;
		}
		echo Html::tag($this->tag, implode('', $links), $this->options);
	}

	/**
	 * Renders a single breadcrumb item.
	 * @param array $link the link to be rendered. It must contain the "label" element. The "url" element is optional.
	 * @param string $template the template to be used to rendered the link. The token "{link}" will be replaced by the link.
	 * @return string the rendering result
	 * @throws InvalidConfigException if `$link` does not have "label" element.
	 */
	protected function renderItem($link, $template, $add = '')
	{
		$encodeLabel = ArrayHelper::remove($link, 'encode', $this->encodeLabels);
		if (array_key_exists('label', $link)) {
			$label = $encodeLabel ? Html::encode($link['label']) : $link['label'];
			if (isset($link['icon']) && $link['icon']) {
				$label = "<i class='" . $link['icon'] . "'> </i> " . $label;
			}
		} else {
			throw new InvalidConfigException('The "label" element is required for each link.');
		}
		if (isset($link['template'])) {
			$template = $link['template'];
		}
		if (isset($link['url'])) {
			$options = $link;
			unset($options['template'], $options['label'], $options['url'], $options['notes']);
			$link = Html::a($label, $link['url'], $options);
		} else {
			$link = $label;
		}

		if ($add != '')
			$link .= $add;

		return strtr($template, ['{link}' => $link]);
	}
}
