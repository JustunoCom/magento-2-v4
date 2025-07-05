<?php
namespace Justuno\M2\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\OauthService;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\Oauth\Token;

class InstallData implements InstallDataInterface
{
  private $integrationFactory;
  private $oauthService;
  private $authorizationService;
  private $token;

  public function __construct(
    IntegrationFactory $integrationFactory,
    OauthService $oauthService,
    AuthorizationService $authorizationService,
    Token $token
  ) {
    $this->integrationFactory = $integrationFactory;
    $this->oauthService = $oauthService;
    $this->authorizationService = $authorizationService;
    $this->token = $token;
  }

  public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
  {
    $setup->startSetup();

    $integrationName = 'Justuno API Integration';

    // Check if integration already exists
    $existingIntegration = $this->integrationFactory->create()
      ->load($integrationName, 'name');

    if (!$existingIntegration->getId()) {
      $integrationData = [
        'name' => $integrationName,
        'email' => 'admin@justuno.com',
        'status' => 1,
        'endpoint' => 'https://api.justuno.com/callback',
        'setup_type' => 0
      ];

      try {
        // Create Integration
        $integration = $this->integrationFactory->create();
        $integration->setData($integrationData);
        $integration->save();

        // Create OAuth Consumer
        $consumer = $this->oauthService->createConsumer([
          'name' => 'Integration' . $integration->getId()
        ]);

        // Link consumer to integration
        $integration->setConsumerId($consumer->getId());
        $integration->save();

        // Grant specific permissions
        $this->authorizationService->grantPermissions(
          $integration->getId(),
          [
            'Magento_Sales::sales',
            'Magento_Sales::sales_order',
            'Magento_Customer::customer',
            'Magento_Catalog::products'
          ]
        );

        // Create access token
        $accessToken = $this->token->createVerifierToken($consumer->getId());
        $accessToken->setType('access');
        $accessToken->save();

      } catch (\Exception $e) {
        throw new \Exception('Error creating Justuno integration: ' . $e->getMessage());
      }
    }

    $setup->endSetup();
  }
}