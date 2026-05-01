<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\file\FileInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Entity-related hook implementations.
 */
final class EntityHooks {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly ImageFactory $imageFactory,
    #[Autowire(service: 'logger.channel.kgaut_tools')]
    private readonly LoggerChannelInterface $logger,
  ) {}

  /**
   * Implements hook_entity_insert().
   *
   * Generates all image styles when a new image file is uploaded, so the
   * derivatives are immediately available without a first-time render hit.
   *
   * @see http://flocondetoile.fr/blog/generer-des-styles-d-images-automatiquement-avec-drupal-8
   */
  #[Hook('entity_insert')]
  public function entityInsert(EntityInterface $entity): void {
    if (!$entity instanceof FileInterface) {
      return;
    }

    if ((int) $this->configFactory->get('kgaut_tools.config')->get('disable_image_derivate') === 1) {
      return;
    }

    $image = $this->imageFactory->get($entity->getFileUri());
    if (!$image->isValid()) {
      return;
    }

    $image_uri = $entity->getFileUri();
    /** @var \Drupal\image\ImageStyleInterface[] $styles */
    $styles = ImageStyle::loadMultiple();
    foreach ($styles as $style) {
      $this->createDerivative($style, $image_uri);
    }
  }

  /**
   * Creates a single image derivative, logging any failure.
   */
  private function createDerivative(ImageStyleInterface $style, string $image_uri): void {
    $destination = $style->buildUri($image_uri);
    if (!$style->createDerivative($image_uri, $destination)) {
      $this->logger->warning('Failed to create derivative @style for @uri.', [
        '@style' => $style->id(),
        '@uri' => $image_uri,
      ]);
    }
  }

}
