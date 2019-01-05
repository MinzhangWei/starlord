<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Search extends Base
{

    public function __construct()
    {
        parent::__construct();

    }

    //分页
    public function all()
    {
        $input = $this->input->post();
        $user = $this->_user;

        $userId = $user['user_id'];
        $tripType = $input['trip_type'];
        $beginTime = $input['begin_time'];
        $beginDate = $input['begin_date'];
        $targetStart = $input['target_start'];
        $targetEnd = $input['target_end'];
        $onlyInMyGroup = $input['only_in_my_group'];

        $page = $input['page'];

        $this->load->model('service/SearchService');
        $this->load->model('service/GroupUserService');
        $this->load->model('service/SearchService');

        $trips = $this->SearchService->search($tripType, $beginDate, $beginTime, $targetStart, $targetEnd);

        //过滤只属于自己群的行程
        if ($onlyInMyGroup == Config::SEARCH_ONLY_IN_MY_GROUP && !empty($trips) && !empty($groups)) {
            $groups = $this->GroupUserService->getGroupsByUserId($userId);
            $groupIdMap = array();
            foreach ($groups as $group) {
                $groupIdMap[$group['group_id']] = 1;
            }

            $filteredTrips = array();
            foreach ($trips as $trip) {
                $groupInfos = json_decode($trip['group_info'], true);
                if (empty($groupInfos)) {
                    continue;
                }
                foreach ($groupInfos as $groupInfo) {
                    if (isset($groupIdMap[$groupInfo['group_id']])) {
                        $filteredTrips[] = $trip;
                    }
                }
            }

            $trips = $filteredTrips;
        }

        if(empty($page)){
            $this->_returnSuccess(
                array(
                    'has_next' => false,
                    'trips' => $trips,
                )
            );
        }else{
            $retTrips = array_slice($trips, $page * Config::TRIP_EACH_PAGE, Config::TRIP_EACH_PAGE);
            $hasNext = true;
            if (count($retTrips) < 20) {
                $hasNext = false;
            }
            $this->_returnSuccess(
                array(
                    'has_next' => $hasNext,
                    'trips' => $retTrips,
                )
            );
        }
    }
}
