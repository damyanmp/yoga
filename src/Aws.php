<?php

namespace Yoga;

/**
 * @method static Aws service()
 */
class Aws extends \Yoga\Service {

    public function getThisServerIpAddress() {
        return file_get_contents('http://instance-data/latest/meta-data/local-ipv4');
    }

    public function getThisInstanceId() {
        return file_get_contents('http://instance-data/latest/meta-data/instance-id');
    }

    public function startInstance($instanceId) {
        return $this->retry(function () use ($instanceId) {
            return $this->getEc2Client()->startInstances([
                'InstanceIds' => [$instanceId]
            ]);
        });
    }

    public function setInstanceTag($instanceId, $tagName, $tagValue) {
        return $this->retry(function () use ($instanceId, $tagName, $tagValue) {
            return $this->getEc2Client()->createTags([
                'Resources' => [$instanceId],
                'Tags' => [[
                    'Key' => $tagName,
                    'Value' => $tagValue,
                ]]
            ]);
        });
    }

    public function getInstanceTag($instanceId, $tagName) {
        return $this->retry(function () use ($instanceId, $tagName) {
            $awsResponse = $this->getEc2Client()->describeTags([
                'Filters' => [
                    [
                        'Name' => 'resource-id',
                        'Values' => [$instanceId],
                    ],
                    [
                        'Name' => 'key',
                        'Values' => [$tagName],
                    ]
                ]
            ]);
            $tags = $awsResponse->get('Tags');
            if ($tags && is_array($tags) && isset($tags[0]) && is_array($tags[0]) && isset($tags[0]['Value'])) {
                return $tags[0]['Value'];
            }
            return null;
        });
    }

    /**
     * @param int $instanceId
     * @return \Yoga\Enum\AwsInstanceStatus
     */
    public function getInstanceStatus($instanceId) {
        return $this->retry(function () use ($instanceId) {
            $response = $this->getEc2Client()->describeInstanceStatus([
                'InstanceIds' => [$instanceId],
                'IncludeAllInstances' => true
            ]);
            $statusCode = $response->get('InstanceStatuses')[0]['InstanceState']['Code'];
            if (!\Yoga\Enum\AwsInstanceStatus::isValid($statusCode)) {
                $statusCode = \Yoga\Enum\AwsInstanceStatus::UNKNOWN;
            }
            return \Yoga\Enum\AwsInstanceStatus::wrap($statusCode);
        });
    }

    /**
     * @param string $amiId
     * @param int $instanceCount
     * @param string $instanceType
     * @param string $name
     * @param string $keyName
     * @param string $securityGroup
     * @return string[]
     */
    public function launchInstancesFromAmi(
        $amiId,
        $instanceCount,
        $instanceType,
        $name,
        $keyName,
        $securityGroup
    ) {
        return $this->retry(
            function ()
            use (
                $amiId,
                $instanceCount,
                $instanceType,
                $name,
                $keyName,
                $securityGroup
            ) {
                $ec2Client = $this->getEc2Client();
                $response = $ec2Client->runInstances([
                    'ImageId' => $amiId,
                    'MinCount' => $instanceCount,
                    'MaxCount' => $instanceCount,
                    'InstanceType' => $instanceType,
                    'KeyName' => $keyName,
                    'SecurityGroups' => [$securityGroup]
                ]);
                $instances = $response->get('Instances');
                $instanceIds = [];
                foreach ($instances as $instance) {
                    $instanceId = $instance['InstanceId'];
                    $instanceIds[] = $instanceId;
                    $this->setInstanceTag($instanceId, 'Name', $name);
                }
                $ec2Client->waitUntilInstanceRunning([
                    'InstanceIds' => $instanceIds,
                ]);
                $this->stopInstances($instanceIds);
                return $instanceIds;
            }
        );
    }

    /**
     * @param string[] $instanceIds
     */
    public function stopInstances(array $instanceIds) {
        $this->retry(function () use ($instanceIds) {
            $this->getEc2Client()->stopInstances([
                'InstanceIds' => $instanceIds,
            ]);
        });
    }

    private function getEc2Client() {
        static $ec2Client;
        if (!$ec2Client) {
            $awsConfiguration = \Yoga\Configuration::service()->getAwsConfiguration();
            $ec2Client = \Aws\Ec2\Ec2Client::factory([
                'key' => $awsConfiguration->getKey(),
                'secret' => $awsConfiguration->getSecret(),
                'region' => 'us-east-1'
            ]);
        }
        return $ec2Client;
    }

    /**
     * @param string $filename
     * @param string $localFilepath
     * @param string $bucket
     * @return array[]
     */
    public function uploadToS3($filename, $localFilepath, $bucket) {
        $s3 = $this->getS3Client();
        $result = $s3->putObject(array(
            'Bucket'     => $bucket,
            'Key'        => $filename,
            'SourceFile' => $localFilepath
        ));
        return $result;
    }

    private function getS3Client() {
        static $s3Client;
        if (!$s3Client) {
            $awsConfiguration = \Yoga\Configuration::service()->getAwsConfiguration();
            $s3Client = \Aws\S3\S3Client::factory([
                'key' => $awsConfiguration->getKey(),
                'secret' => $awsConfiguration->getSecret(),
                'region' => 'us-east-1'
            ]);
        }
        return $s3Client;
    }

    private function retry(callable $callback, $retryCount = 10) {
        try {
            return $callback();
        } catch (\Exception $e) {
            if ($retryCount) {
                sleep(60 * (11 - $retryCount));
                return $this->retry($callback, $retryCount - 1);
            }
            throw $e;
        }
    }

}