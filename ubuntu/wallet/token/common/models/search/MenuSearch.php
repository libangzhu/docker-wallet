<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modelsgii\Menu;
use common\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;

/**
 * MenuSearch represents the model behind the search form about `common\models\Menu`.
 */
class MenuSearch extends Menu
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pid', 'sort', 'hide', 'status'], 'integer'],
            [['title', 'url', 'group', 'icon'], 'safe'],
			[['title', 'url', 'sort', 'group'], 'filter', 'filter' => 'trim']
		];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Menu::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->title && is_string($this->title)) {
			$query->andFilterWhere(['like', 'title', $this->title]);

		} else {
        	$pid = Yii::$app->request->get('pid',0);
			$query->andFilterWhere(['pid' => $pid]);
		}

        $query->andFilterWhere(['hide' => $this->hide]);

        $query->orderBy([
            'sort'   => SORT_ASC,
        ]);
        
        return $dataProvider;
    }

	/**
	 * [重写菜单管理]
	 * @author libingke
	 * @param $params
	 * @return ArrayDataProvider
	 */
	public function search_bak($params)
	{
		$this->load($params);

		$query = Menu::find();
		if ($this->validate()) {
			if ($this->title && is_string($this->title)) {
				$query->andFilterWhere(['like', 'title', $this->title]);

			} else {
				$pid = Yii::$app->request->get('pid',null);
				$query->andFilterWhere(['pid' => $pid]);
			}

			$query->andFilterWhere(['hide' => $this->hide]);
		}

		$lists = $query->orderBy('sort asc')->asArray()->all();

		$lists = ArrayHelper::list_to_tree($lists, 'id', 'pid');
		$lists = ArrayHelper::format_tree($lists, 'title');

		$dataProvider = new ArrayDataProvider([
			'allModels' => $lists,
			'pagination' => [
				'pageSize' => 20,
			],
		]);

        return $dataProvider;
	}
}
