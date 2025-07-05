<?php
namespace Justuno\M2\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;

class InstallData implements InstallDataInterface
{
  /**
   * @var ConfigBasedIntegrationManager
   */
  private $integrationManager;

  /**
   * @param ConfigBasedIntegrationManager $integrationManager
   */
  public function __construct(ConfigBasedIntegrationManager $integrationManager)
  {
    $this->integrationManager = $integrationManager;
  }

  /**
   * {@inheritdoc}
   */
  public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
  {
    $this->integrationManager->processIntegrationConfig(['Justuno_M2']);
  }
}