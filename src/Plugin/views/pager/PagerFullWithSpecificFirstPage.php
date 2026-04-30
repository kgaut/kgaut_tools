<?php

declare(strict_types=1);

namespace Drupal\kgaut_tools\Plugin\views\pager;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\pager\Full;

/**
 * Pager that allows a different "items per page" value on the first page.
 *
 * @ingroup views_pager_plugins
 *
 * @ViewsPager(
 *   id = "full_with_first_page",
 *   title = @Translation("Paged output, full pager with a specific number of items for the first page"),
 *   short_title = @Translation("Pager Full - specific first page"),
 *   help = @Translation("Pager full with a specific number of items for the first page"),
 *   theme = "pager",
 *   register_theme = FALSE
 * )
 */
final class PagerFullWithSpecificFirstPage extends Full {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['items_per_page_first_page'] = ['default' => 10];
    unset($options['expose']);
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $pager_text = $this->displayHandler->getPagerText();

    $form['items_per_page']['#weight'] = -2;
    $form['items_per_page_first_page'] = [
      '#title' => $pager_text['items per page title'] . ' for the first page',
      '#type' => 'number',
      '#min' => 0,
      '#weight' => -1,
      '#description' => $pager_text['items per page description'],
      '#default_value' => $this->options['items_per_page_first_page'],
    ];

    unset($form['expose']);
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    parent::validateOptionsForm($form, $form_state);
    $first_page = $form_state->getValue(['pager_options', 'items_per_page_first_page']);
    if ($first_page !== NULL && (!is_numeric($first_page) || (int) $first_page < 0)) {
      $form_state->setErrorByName('pager_options][items_per_page_first_page', $this->t('Items per page (first page) must be a positive integer.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    $first_differs = $this->options['items_per_page'] !== $this->options['items_per_page_first_page'];

    if (!empty($this->options['offset'])) {
      if ($first_differs) {
        return $this->formatPlural($this->options['items_per_page'], '@count item (@count_first for the first page), skip @skip', 'Paged, @count items, skip @skip', [
          '@count' => $this->options['items_per_page'],
          '@count_first' => $this->options['items_per_page_first_page'],
          '@skip' => $this->options['offset'],
        ]);
      }
      return $this->formatPlural($this->options['items_per_page'], '@count item, skip @skip', 'Paged, @count items, skip @skip', [
        '@count' => $this->options['items_per_page'],
        '@skip' => $this->options['offset'],
      ]);
    }

    if ($first_differs) {
      return $this->formatPlural($this->options['items_per_page'], '@count item', 'Paged, @count items (@count_first for the first page)', [
        '@count' => $this->options['items_per_page'],
        '@count_first' => $this->options['items_per_page_first_page'],
      ]);
    }
    return $this->formatPlural($this->options['items_per_page'], '@count item', 'Paged, @count items', [
      '@count' => $this->options['items_per_page'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $current_page = (int) $this->getCurrentPage();

    if ($current_page === 0) {
      $this->options['items_per_page'] = $this->options['items_per_page_first_page'];
    }

    $limit = $this->options['items_per_page'];
    $offset = $this->options['offset'];

    if ($current_page > 0) {
      $offset += ($current_page - 1) * $this->options['items_per_page'];
      $offset += $this->options['items_per_page_first_page'];
    }

    if (!empty($this->options['total_pages']) && $current_page >= $this->options['total_pages']) {
      $limit = $this->options['items_per_page'];
      $offset = $this->options['total_pages'] * $this->options['items_per_page'];
    }

    $this->view->query->setLimit($limit);
    $this->view->query->setOffset($offset);
  }

  /**
   * {@inheritdoc}
   */
  public function updatePageInfo() {
    if (!empty($this->options['total_pages'])
      && ($this->options['total_pages'] * $this->options['items_per_page']) < $this->total_items
    ) {
      $this->total_items = $this->options['total_pages'] * $this->options['items_per_page'];
    }

    $items_per_page = $this->getItemsPerPage();
    if (empty($items_per_page)) {
      return;
    }

    $items_per_page_first = $this->getItemsPerPageFirst();
    $total_items = (int) $this->getCurrentPage() !== 0
      ? $this->getTotalItems() + $items_per_page_first - 1
      : $this->getTotalItems();
    $pager = $this->pagerManager->createPager($total_items, $this->options['items_per_page'], $this->options['id']);

    if ($this->getCurrentPage() >= $pager->getTotalPages()) {
      $this->setCurrentPage($pager->getTotalPages() - 1);
    }
  }

  /**
   * Returns the number of items shown on the first page.
   */
  public function getItemsPerPageFirst(): int {
    return (int) ($this->options['items_per_page_first_page'] ?? 0);
  }

}
