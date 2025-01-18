<?php

namespace App\Services;

use Taylanunutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    protected $appId;
    protected $appCertificate;

    public function __construct()
    {
        $this->appId = env('AGORA_APP_ID');
        $this->appCertificate = env('AGORA_APP_CERTIFICATE');
    }

    /**
     * Generate an Agora RTC token for a specific channel and user.
     *
     * @param string $channelName
     * @param string $userId
     * @param string $role
     * @param int $expiryInSeconds
     * @return string
     */
    public function generateRtcToken(string $channelName, string $userId, string $role, int $expiryInSeconds = 3600): string
    {
        $privilegeExpiredTs = now()->addSeconds($expiryInSeconds)->timestamp;

        return RtcTokenBuilder::buildTokenWithUid(
            $this->appId,
            $this->appCertificate,
            $channelName,
            $userId,
            $role,
            $privilegeExpiredTs
        );
    }

    /**
     * Generate an Agora RTM token (if required for messaging).
     *
     * @param string $userId
     * @param int $expiryInSeconds
     * @return string
     */
    public function generateRtmToken(string $userId, int $expiryInSeconds = 3600): string
    {
        $privilegeExpiredTs = now()->addSeconds($expiryInSeconds)->timestamp;

        return RtcTokenBuilder::buildTokenWithAccount(
            $this->appId,
            $this->appCertificate,
            $userId,
            $privilegeExpiredTs
        );
    }
}
