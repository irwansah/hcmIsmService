<?php

namespace cms\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use cms\models\Posting;

/**
 * PostingSearch represents the model behind the search form of `cms\models\Posting`.
 */
class PostingSearch extends Posting
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_posting', 'id_group', 'owner_id', 'active', 'views_count', 'like_count', 'comment_count', 'type_posting'], 'integer'],
            [['group_name', 'owner_name', 'caption', 'url_content', 'thumnail_content', 'text', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Posting::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id_posting' => $this->id_posting,
            'id_group' => $this->id_group,
            'owner_id' => $this->owner_id,
            'active' => $this->active,
            'views_count' => $this->views_count,
            'like_count' => $this->like_count,
            'comment_count' => $this->comment_count,
            'type_posting' => $this->type_posting,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        $query->andFilterWhere(['like', 'group_name', $this->group_name])
            ->andFilterWhere(['like', 'owner_name', $this->owner_name])
            ->andFilterWhere(['like', 'caption', $this->caption])
            ->andFilterWhere(['like', 'url_content', $this->url_content])
            ->andFilterWhere(['like', 'thumnail_content', $this->thumnail_content])
            ->andFilterWhere(['like', 'text', $this->text]);

        return $dataProvider;
    }
}
