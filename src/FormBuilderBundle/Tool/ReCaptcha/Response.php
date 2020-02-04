<?php

namespace FormBuilderBundle\Tool\ReCaptcha;

class Response
{
    /**
     * @var bool
     */
    private $success = false;

    /**
     * @var array
     */
    private $errorCodes = [];

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var string
     */
    private $challengeTs;

    /**
     * @var string
     */
    private $apkPackageName;

    /**
     * @var float
     */
    private $score;

    /**
     * @var string
     */
    private $action;

    /**
     * @param string $json
     *
     * @return Response
     */
    public static function fromJson($json)
    {
        $responseData = json_decode($json, true);

        if (!$responseData) {
            return new self(false, ['invalid-json']);
        }

        $hostname = isset($responseData['hostname']) ? $responseData['hostname'] : null;
        $challengeTs = isset($responseData['challenge_ts']) ? $responseData['challenge_ts'] : null;
        $apkPackageName = isset($responseData['apk_package_name']) ? $responseData['apk_package_name'] : null;
        $score = isset($responseData['score']) ? floatval($responseData['score']) : null;
        $action = isset($responseData['action']) ? $responseData['action'] : null;

        if (isset($responseData['success']) && $responseData['success'] == true) {
            return new self(true, [], $hostname, $challengeTs, $apkPackageName, $score, $action);
        }

        if (isset($responseData['error-codes']) && is_array($responseData['error-codes'])) {
            return new self(false, $responseData['error-codes'], $hostname, $challengeTs, $apkPackageName, $score, $action);
        }

        return new self(false, ['unknown-error'], $hostname, $challengeTs, $apkPackageName, $score, $action);
    }

    /**
     * @param bool   $success
     * @param string $hostname
     * @param string $challengeTs
     * @param string $apkPackageName
     * @param float  $score
     * @param string $action
     * @param array  $errorCodes
     */
    public function __construct($success, array $errorCodes = [], $hostname = null, $challengeTs = null, $apkPackageName = null, $score = null, $action = null)
    {
        $this->success = $success;
        $this->hostname = $hostname;
        $this->challengeTs = $challengeTs;
        $this->apkPackageName = $apkPackageName;
        $this->score = $score;
        $this->action = $action;
        $this->errorCodes = $errorCodes;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getChallengeTs()
    {
        return $this->challengeTs;
    }

    /**
     * @return string
     */
    public function getApkPackageName()
    {
        return $this->apkPackageName;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'success'          => $this->isSuccess(),
            'hostname'         => $this->getHostname(),
            'challenge_ts'     => $this->getChallengeTs(),
            'apk_package_name' => $this->getApkPackageName(),
            'score'            => $this->getScore(),
            'action'           => $this->getAction(),
            'error-codes'      => $this->getErrorCodes(),
        ];
    }
}
