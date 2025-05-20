<?php

namespace Civi\Thinkific\Hook\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ServiceContainer {

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  private ContainerBuilder $container;

  /**
   * ServiceContainer constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   */
  public function __construct(ContainerBuilder $container) {
    $this->container = $container;
  }

  /**
   * Registers services to container.
   */
  public function register(): void {
    $this->container->setDefinition('service.thinkific.api_client', new Definition(
      \Civi\Thinkific\Service\ApiClient::class
    ))->setAutowired(TRUE)->setPublic(TRUE);
    $this->container->setDefinition('service.thinkific.participant_manager', new Definition(
      \Civi\Thinkific\Service\ParticipantManager::class
    ))->setAutowired(TRUE)->setPublic(TRUE);
    $this->container->setDefinition('service.thinkific.user_handler', new Definition(
      \Civi\Thinkific\Service\UserHandler::class
    ))->setAutowired(TRUE)->setPublic(TRUE);
    $this->container->setDefinition('service.thinkific.enrollment_handler', new Definition(
      \Civi\Thinkific\Service\EnrollmentHandler::class
    ))->setAutowired(TRUE)->setPublic(TRUE);

    $this->container->setAlias('Civi\Thinkific\Service\ApiClient', 'service.thinkific.api_client');
    $this->container->setAlias('Civi\Thinkific\Service\ParticipantManager', 'service.thinkific.participant_manager');
    $this->container->setAlias('Civi\Thinkific\Service\UserHandler', 'service.thinkific.user_handler');
    $this->container->setAlias('Civi\Thinkific\Service\EnrollmentHandler', 'service.thinkific.enrollment_handler');
  }

}
