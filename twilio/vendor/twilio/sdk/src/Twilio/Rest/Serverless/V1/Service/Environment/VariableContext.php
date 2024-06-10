<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Serverless
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Serverless\V1\Service\Environment;

use Twilio\Exceptions\TwilioException;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;


class VariableContext extends InstanceContext
    {
    /**
     * Initialize the VariableContext
     *
     * @param Version $version Version that contains the resource
     * @param string $serviceSid The SID of the Service to create the Variable resource under.
     * @param string $environmentSid The SID of the Environment in which the Variable resource exists.
     * @param string $sid The SID of the Variable resource to delete.
     */
    public function __construct(
        Version $version,
        $serviceSid,
        $environmentSid,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'serviceSid' =>
            $serviceSid,
        'environmentSid' =>
            $environmentSid,
        'sid' =>
            $sid,
        ];

        $this->uri = '/Services/' . \rawurlencode($serviceSid)
        .'/Environments/' . \rawurlencode($environmentSid)
        .'/Variables/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Delete the VariableInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->version->delete('DELETE', $this->uri);
    }


    /**
     * Fetch the VariableInstance
     *
     * @return VariableInstance Fetched VariableInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): VariableInstance
    {

        $payload = $this->version->fetch('GET', $this->uri, [], []);

        return new VariableInstance(
            $this->version,
            $payload,
            $this->solution['serviceSid'],
            $this->solution['environmentSid'],
            $this->solution['sid']
        );
    }


    /**
     * Update the VariableInstance
     *
     * @param array|Options $options Optional Arguments
     * @return VariableInstance Updated VariableInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): VariableInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'Key' =>
                $options['key'],
            'Value' =>
                $options['value'],
        ]);

        $payload = $this->version->update('POST', $this->uri, [], $data);

        return new VariableInstance(
            $this->version,
            $payload,
            $this->solution['serviceSid'],
            $this->solution['environmentSid'],
            $this->solution['sid']
        );
    }


    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $context = [];
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Serverless.V1.VariableContext ' . \implode(' ', $context) . ']';
    }
}
