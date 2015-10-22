<?php

App::uses('HttpSocket', 'Network/Http');
App::uses('DataSource', 'Model/Datasource');

class GithubSource extends DataSource {
    /**
     * Construct
     * @param array $config 
     */
    function __construct($config) {
        parent::__construct($config);
        $this->config = Set::merge($this->config, $config);
        $this->Http = new HttpSocket();
    }

    /**
     * List sources.
     *
     * @param  array $data
     * @return null
     */
    public function listSources($data = null) {
        return null;
    }

    /**
     * Calulate method
     *
     * @param  Model  $mode
     * @param  $func
     * @param  array  $params
     * @return count
     */
    public function calculate(Model $model, $func, $params = array()) {
        return 'COUNT';
    }

    /**
     * read method
     *
     * @param  Model  $model
     * @param  array  $queryData
     * @return array
     */
    public function read(Model $model, $queryData = array(),$recursive = NULL) {
        $data = array();
        $limit = !empty($queryData['limit']) ? $queryData['limit'] : 0;
        $offset = !empty($queryData['offset']) ? $queryData['offset'] : 0;

        switch($queryData['conditions']['type']) {
            case 'user_info':
                if(!empty($queryData['conditions']['username'])) {
                    $items = json_decode($this->Http->get($this->config['api_host'].'/users/'.$queryData['conditions']['username']), true);
                    $array=$items;
                }
                break;
            case 'user_activity':
                if(!empty($queryData['conditions']['username'])) {
                    $items = json_decode($this->Http->get($this->config['api_host'].'/users/'.$queryData['conditions']['username'].'/events'), true);
                    $i=0;

                    foreach ($items as $item) {
                        if($i==$this->config['limit']) {
                            break;
                        }

                        switch($item['type']) {
                            case 'IssueCommentEvent':
                                $array[]=array(
                                    'text'=>'Commented on an issue at',
                                    'name'=>$item['repo']['name'],
                                    'url'=>'http://github.com/'.$item['repo']['name'],
                                    'created'=>$item['created_at']
                                );
                                break;
                            case 'PullRequestEvent':
                                $array[]=array(
                                    'text'=>'Sent a pull request to',
                                    'name'=>$item['repo']['name'],
                                    'url'=>'http://github.com/'.$item['repo']['name'],
                                    'created'=>$item['created_at']
                                );
                                break;
                            case 'WatchEvent':
                                $array[]=array(
                                    'text'=>'Starred',
                                    'name'=>$item['repo']['name'],
                                    'url'=>'http://github.com/'.$item['repo']['name'],
                                    'created'=>$item['created_at']
                                );
                                break;
                            case 'FollowEvent':
                                $array[]=array(
                                    'text'=>'Followed',
                                    'name'=>$item['payload']['target']['login'],
                                    'url'=>$item['payload']['target']['html_url'],
                                    'created'=>$item['created_at']
                                );
                                break;
                            case 'ForkEvent':
                                $array[]=array(
                                    'text'=>'Forked',
                                    'name'=>$item['repo']['name'],
                                    'url'=>'http://github.com/'.$item['repo']['name'],
                                    'created'=>$item['created_at']
                                );
                                break;
                            case 'PushEvent':
                                $array[]=array(
                                    'text'=>'Pushed to',
                                    'name'=>$item['repo']['name'],
                                    'url'=>'http://github.com/'.$item['repo']['name'],
                                    'created'=>$item['created_at']
                                );
                                break;
                            case 'CreateEvent':
                                $array[]=array(
                                    'text'=>'Created',
                                    'name'=>$item['repo']['name'],
                                    'url'=>'http://github.com/'.$item['repo']['name'],
                                    'created'=>$item['created_at']
                                );
                                break;
                        }
                        $i++;
                    }
                }
                break;
            case 'user_repos':
                if(!empty($queryData['conditions']['username'])) {
                    $items = json_decode($this->Http->get($this->config['api_host'].'/users/'.$queryData['conditions']['username'].'/repos'), true);
                    $array=$items;
                }
                break;
            case 'repo':
                if(!empty($queryData['conditions']['username'])) {
                    if(!empty($queryData['conditions']['repo'])) {
                        $items = json_decode($this->Http->get($this->config['api_host'].'/repos/'.$queryData['conditions']['username'].'/'.$queryData['conditions']['repo']), true);
                        $array=$items;
                    }
                }
                break;
        }
        return $array;
    }
}
