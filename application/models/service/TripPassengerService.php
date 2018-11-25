<?php

class TripPassengerService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

    }

    public function getTripByTripId($userId, $tripId)
    {
        $this->load->model('dao/TripPassengerDao');
        $trip = $this->TripPassengerDao->getOneByTripId($userId, $tripId);
        if (empty($trip)) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        return $trip;
    }

    public function updateTrip($tripId, $userId, $tripPassengerDetail)
    {
        if ($tripId == null || userId == null) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        $trip = $tripPassengerDetail;

        $this->load->model('dao/TripPassengerDao');

        //只有正常状态的行程才允许编辑
        $this->TripPassengerDao->updateByTripIdAndStatus($userId, $tripId, Config::TRIP_STATUS_NORMAL, $trip);

        return true;
    }

    public function saveTripTemplate($tripId, $userId, $tripPassengerDetail)
    {
        $trip = array();
        $trip['trip_id'] = $tripId;
        $trip['user_id'] = $userId;
        $trip = array_merge($trip, $tripPassengerDetail);
        $trip['status'] = Config::TRIP_STATUS_DRAFT;

        $this->load->model('dao/TripPassengerDao');
        if ($tripId == null) {
            //创建新模板
            $this->load->model('redis/IdGenRedis');
            $trip['trip_id'] = $this->IdGenRedis->gen(Config::ID_GEN_KEY_TRIP);
            $this->TripPassengerDao->insertOne($userId, $trip);
        } else {
            //更新旧模板
            $this->TripPassengerDao->updateByTripIdAndStatus($userId, $tripId, Config::TRIP_STATUS_DRAFT, $trip);
        }

        return true;
    }

    public function createNewTrip($userId, $tripPassengerDetail, $user)
    {
        $this->load->model('dao/TripPassengerDao');
        $this->load->model('redis/IdGenRedis');

        $trip = array();
        $trip['user_id'] = $userId;
        $trip['trip_id'] = $this->IdGenRedis->gen(Config::ID_GEN_KEY_TRIP);
        $trip = array_merge($trip, $tripPassengerDetail);
        $trip['status'] = Config::TRIP_STATUS_NORMAL;
        //插入用户信息快照
        $trip['user_info'] = json_encode(
            array(
                "user_id" => $user["user_id"],
                "phone" => $user["phone"],
                "nick_name" => $user["nick_name"],
                "gender" => $user["gender"],
                "city" => $user["city"],
                "province" => $user["province"],
                "country" => $user["country"],
                "avatar_url" => $user["avatar_url"],
                "car_plate" => $user["car_plate"],
                "car_brand" => $user["car_brand"],
                "car_model" => $user["car_model"],
                "car_color" => $user["car_color"],
                "car_type" => $user["car_type"],
            )
        );
        $newTrip = $this->TripPassengerDao->insertOne($userId, $trip);

        return $newTrip;
    }

    public function deleteTrip($userId, $tripId)
    {
        $this->load->model('dao/TripPassengerDao');

        $trip = array();
        $trip['user_id'] = $userId;
        $trip['trip_id'] = $tripId;

        $ret = $this->TripPassengerDao->deleteOne($userId, $trip);

        return $ret;
    }

    public function getMyTripList($userId)
    {
        $this->load->model('dao/TripPassengerDao');
        $trips = $this->TripPassengerDao->getListByUserIdAndStatusArr($userId, array(Config::TRIP_STATUS_NORMAL, Config::TRIP_STATUS_CANCEL));

        return $trips;
    }

    public function getMyTemplateList($userId)
    {
        $this->load->model('dao/TripPassengerDao');
        $trips = $this->TripPassengerDao->getListByUserIdAndStatusArr($userId, array(Config::TRIP_STATUS_CANCEL));
        $tripsWithType = array();
        if (!empty($trips)) {
            foreach ($trips as $trip) {
                $tripWithType = $trip;
                $tripWithType['trip_type'] = Config::TRIP_TYPE_PASSENGER;
            }
        }

        return $tripsWithType;
    }
}