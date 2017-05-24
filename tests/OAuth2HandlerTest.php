<?php

namespace kamermans\OAuth2\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\OAuth2Handler;
use kamermans\OAuth2\Token\RawToken;
use kamermans\OAuth2\Tests\BaseTestCase;

/**
 * OAuth2 plugin.
 *
 * @author Steve Kamerman <stevekamerman@gmail.com>
 * @author Matthieu Moquet <matthieu@moquet.net>
 *
 * @link http://tools.ietf.org/html/rfc6749 OAuth2 specification
 */
class OAuth2HandlerTest extends BaseTestCase
{

    public function testConstruct()
    {
        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = new OAuth2Handler($grant);
    }

    public function testSetAccessTokenAsArray()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_in' => 3600,
        ];

        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = new OAuth2Handler($grant);

        $sub->setAccessToken($tokenData);
        $this->assertEquals($tokenData['access_token'], $sub->getAccessToken());
    }

    public function testSetAccessTokenAsString()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_in' => 3600,
        ];

        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = new OAuth2Handler($grant);

        $sub->setAccessToken($tokenData['access_token']);
        $this->assertEquals($tokenData['access_token'], $sub->getAccessToken());
    }

    public function testSetAccessTokenAsObject()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_in' => 3600,
            'expires_at' => time() + 3600,
        ];

        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = new OAuth2Handler($grant);

        $token = new RawToken($tokenData['access_token'], $tokenData['refresh_token'], $tokenData['expires_at']);
        $sub->setAccessToken($token);
        $this->assertEquals($tokenData['access_token'], $sub->getAccessToken());
    }

    public function testGetAccessTokenCausesReauth()
    {
        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = $this->getMockBuilder('\kamermans\OAuth2\OAuth2Handler')
            ->setConstructorArgs([$grant])
            ->setMethods(['requestNewAccessToken'])
            ->getMock();

        $sub->expects($this->once())
            ->method('requestNewAccessToken')
            ->will($this->returnValue(null));

        $sub->getAccessToken();
    }

    public function testExpiredTokenCausesReauth()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_in' => -3600,
        ];

        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = $this->getMockBuilder('\kamermans\OAuth2\OAuth2Handler')
            ->setConstructorArgs([$grant])
            ->setMethods(['requestNewAccessToken'])
            ->getMock();

        $sub->expects($this->once())
            ->method('requestNewAccessToken')
            ->will($this->returnValue(null));

        $sub->setAccessToken($tokenData);
        $sub->getAccessToken();
    }

    public function testValidTokenDoesNotCauseReauth()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_in' => 3600,
        ];

        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = $this->getMockBuilder('\kamermans\OAuth2\OAuth2Handler')
            ->setConstructorArgs([$grant])
            ->setMethods(['requestNewAccessToken'])
            ->getMock();

        $sub->expects($this->exactly(0))
            ->method('requestNewAccessToken');

        $sub->setAccessToken($tokenData);
        $sub->getAccessToken();
    }

}