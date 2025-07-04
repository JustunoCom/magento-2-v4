<?php
namespace Justuno\M2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\OauthService;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\ResourceModel\Integration as IntegrationResource;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Math\Random;
use Justuno\M2\Helper\Data as JustunoHelper;

class SetupIntegration extends Command
{
  private $integrationFactory;
  private $oauthService;
  private $authorizationService;
  private $integrationResource;
  private $configWriter;
  private $mathRandom;
  private $justunoHelper;

  public function __construct(
    IntegrationFactory $integrationFactory,
    OauthService $oauthService,
    AuthorizationService $authorizationService,
    IntegrationResource $integrationResource,
    WriterInterface $configWriter,
    Random $mathRandom,
    JustunoHelper $justunoHelper
  ) {
    $this->integrationFactory = $integrationFactory;
    $this->oauthService = $oauthService;
    $this->authorizationService = $authorizationService;
    $this->integrationResource = $integrationResource;
    $this->configWriter = $configWriter;
    $this->mathRandom = $mathRandom;
    $this->justunoHelper = $justunoHelper;
    parent::__construct();
  }

  protected function configure()
  {
    $this->setName('justuno:setup:integration')
      ->setDescription('Setup Justuno API Integration and generate authentication token');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    try {
      $output->writeln('<info>Setting up Justuno API Integration...</info>');

      // Check if we already have a token configured
      $existingToken = $this->justunoHelper->getWooCommerceToken();

      if ($existingToken) {
        $output->writeln('<comment>Found existing token in configuration.</comment>');
        $output->writeln('<info>Current API Token: ' . $existingToken . '</info>');
        $output->writeln('<comment>Test with: curl -H "Authorization: Bearer ' . $existingToken . '" https://yourdomain.com/rest/V1/justuno/orders</comment>');
        return Command::SUCCESS;
      }

      // Check if integration already exists
      $integration = $this->integrationFactory->create();
      $this->integrationResource->load($integration, 'Justuno API Integration', 'name');

      $newToken = $this->mathRandom->getRandomString(32);

      if ($integration->getId()) {
        $output->writeln('<comment>Integration already exists. Updating permissions...</comment>');

        // Update permissions for existing integration
        $this->authorizationService->grantPermissions(
          $integration->getId(),
          ['Justuno_M2::api_access']
        );

        // Save new token to config
        $this->configWriter->save('justuno/general/woocommerce_token', $newToken);

        $output->writeln('<info>Integration updated successfully!</info>');
        $output->writeln('<info>New API Token: ' . $newToken . '</info>');

        return Command::SUCCESS;
      }

      // Create new integration
      $integrationData = [
        'name' => 'Justuno API Integration',
        'email' => 'api@justuno.com',
        'status' => 1,
        'setup_type' => 0, // Manual setup
        'identity_link_url' => '',
        'callback_url' => ''
      ];

      $integration->setData($integrationData);
      $this->integrationResource->save($integration);

      $output->writeln('<info>Integration created with ID: ' . $integration->getId() . '</info>');

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

      $output->writeln('<info>Consumer created with ID: ' . $consumer->getId() . '</info>');

      // Create access token
      $accessToken = $this->oauthService->createAccessToken($consumer->getId(), true);

      $output->writeln('<info>Access token created</info>');

      // Grant specific permissions
      $this->authorizationService->grantPermissions(
        $integration->getId(),
        ['Justuno_M2::api_access']
      );

      $output->writeln('<info>Permissions granted: Justuno_M2::api_access</info>');

      // Save the token to configuration for use with your custom auth plugin
      $this->configWriter->save('justuno/general/woocommerce_token', $newToken);

      // Output the credentials
      $output->writeln('');
      $output->writeln('<info>=== INTEGRATION SETUP COMPLETE ===</info>');
      $output->writeln('Integration ID: ' . $integration->getId());
      $output->writeln('Consumer Key: ' . $consumer->getKey());
      $output->writeln('Consumer Secret: ' . $consumer->getSecret());
      $output->writeln('OAuth Access Token: ' . $accessToken->getToken());
      $output->writeln('OAuth Access Token Secret: ' . $accessToken->getSecret());
      $output->writeln('');
      $output->writeln('<info>=== CUSTOM AUTH TOKEN (Use this for API calls) ===</info>');
      $output->writeln('Custom Token: ' . $newToken);
      $output->writeln('');
      $output->writeln('<comment>Your JustunoApiAuth plugin will authenticate requests using the Custom Token.</comment>');
      $output->writeln('<comment>Use this token in your API calls:</comment>');
      $output->writeln('<comment>Authorization: Bearer ' . $newToken . '</comment>');
      $output->writeln('');
      $output->writeln('<comment>Test endpoints:</comment>');
      $output->writeln('<comment>curl -H "Authorization: Bearer ' . $newToken . '" https://yourdomain.com/rest/V1/justuno/orders</comment>');
      $output->writeln('<comment>curl -H "Authorization: Bearer ' . $newToken . '" https://yourdomain.com/rest/V1/justuno/products</comment>');

    } catch (\Exception $e) {
      $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
      $output->writeln('<error>Trace: ' . $e->getTraceAsString() . '</error>');
      return Command::FAILURE;
    }

    return Command::SUCCESS;
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