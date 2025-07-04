<?php
namespace Justuno\M2\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\OauthService;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\ResourceModel\Integration as IntegrationResource;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Math\Random;
use Psr\Log\LoggerInterface;

class CreateJustunoIntegration implements DataPatchInterface
{
  private $integrationFactory;
  private $oauthService;
  private $authorizationService;
  private $integrationResource;
  private $configWriter;
  private $mathRandom;
  private $logger;

  public function __construct(
    IntegrationFactory $integrationFactory,
    OauthService $oauthService,
    AuthorizationService $authorizationService,
    IntegrationResource $integrationResource,
    WriterInterface $configWriter,
    Random $mathRandom,
    LoggerInterface $logger
  ) {
    $this->integrationFactory = $integrationFactory;
    $this->oauthService = $oauthService;
    $this->authorizationService = $authorizationService;
    $this->integrationResource = $integrationResource;
    $this->configWriter = $configWriter;
    $this->mathRandom = $mathRandom;
    $this->logger = $logger;
  }

  public function apply()
  {
    try {
      // Check if integration already exists
      $integration = $this->integrationFactory->create();
      $this->integrationResource->load($integration, 'Justuno API Integration', 'name');

      if ($integration->getId()) {
        $this->logger->info('Justuno API Integration already exists, updating permissions');

        // Update permissions
        $this->authorizationService->grantPermissions(
          $integration->getId(),
          ['Justuno_M2::api_access']
        );

        return $this;
      }

      // Create new integration
      $integrationData = [
        'name' => 'Justuno API Integration',
        'email' => 'api@justuno.com',
        'status' => 1,
        'setup_type' => 0,
        'identity_link_url' => '',
        'callback_url' => ''
      ];

      $integration->setData($integrationData);
      $this->integrationResource->save($integration);

      // Create consumer
      $consumerData = [
        'name' => 'Justuno API Consumer',
        'key' => $this->generateRandomString(32),
        'secret' => $this->generateRandomString(32)
      ];

      $consumer = $this->oauthService->createConsumer($consumerData);

      // Link consumer to integration
      $integration->setConsumerId($consumer->getId());
      $this->integrationResource->save($integration);

      // Try to create access token
      try {
        $this->oauthService->createAccessToken($consumer->getId(), true);
      } catch (\Exception $e) {
        $this->logger->warning('OAuth token creation failed: ' . $e->getMessage());
      }

      // Grant permissions
      $this->authorizationService->grantPermissions(
        $integration->getId(),
        ['Justuno_M2::api_access']
      );

      // Generate and save custom token
      $customToken = $this->mathRandom->getRandomString(32);
      $this->configWriter->save('justuno/general/woocommerce_token', $customToken);

      $this->logger->info(
        'Justuno API Integration created successfully',
        [
          'integration_id' => $integration->getId(),
          'consumer_id' => $consumer->getId(),
          'custom_token' => $customToken
        ]
      );

    } catch (\Exception $e) {
      $this->logger->error('Failed to create Justuno API Integration: ' . $e->getMessage());
    }

    return $this;
  }

  public static function getDependencies()
  {
    return [];
  }

  public function getAliases()
  {
    return [];
  }

  private function generateRandomString($length = 32)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }
}