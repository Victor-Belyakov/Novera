<?php

namespace app\models\search;

use common\models\Section;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class SectionSearch extends Section
{
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
