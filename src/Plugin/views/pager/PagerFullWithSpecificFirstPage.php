<?php

namespace Drupal\kgaut_tools\Plugin\views\pager;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\pager\Full;

/**
 * The plugin to handle full pager.
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
class PagerFullWithSpecificFirstPage extends Full {

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
    // Only accept integer values.
    $error = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    if (!empty($this->options['offset'])) {
      if ($this->options['items_per_page'] !== $this->options['items_per_page_first_page']) {
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
    if ($this->options['items_per_page'] !== $this->options['items_per_page_first_page']) {
      return $this->formatPlural($this->options['items_per_page'], '@count item', 'Paged, @count items (@count_first for the first page)', [
        '@count' => $this->options['items_per_page'],
        '@count_first' => $this->options['items_per_page_first_page'],
      ]);
    }
    return $this->formatPlural($this->options['items_per_page'], '@count item', 'Paged, @count items', ['@count' => $this->options['items_per_page']]);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if ($this->current_page === 0) {
      $this->options['items_per_page'] = $this->options['items_per_page_first_page'];
    }
    $limit = $this->options['items_per_page'];
    $offset = $this->options['offset'];
    if ($this->current_page > 0) {
      $offset += ($this->current_page - 1) * $this->options['items_per_page'];
      $offset += $this->options['items_per_page_first_page'];
    }
    if (!empty($this->options['total_pages'])) {
      if ($this->current_page >= $this->options['total_pages']) {
        $limit = $this->options['items_per_page'];
        $offset = $this->options['total_pages'] * $this->options['items_per_page'];
      }
    }
    $this->view->query->setLimit($limit);
    $this->view->query->setOffset($offset);
  }

  public function updatePageInfo() {
    if (!empty($this->options['total_pages'])) {
      if (($this->options['total_pages'] * $this->options['items_per_page']) < $this->total_items) {
        $this->total_items = $this->options['total_pages'] * $this->options['items_per_page'];
      }
    }

    // Don't set pager settings for items per page = 0.
    $items_per_page = $this->getItemsPerPage();
    $items_per_page_first = $this->getItemsPerPageFirst();
    if (!empty($items_per_page)) {
      // quick fix if specific number on first page
      $total_items = (int) $this->getCurrentPage() !== 0 ? $this->getTotalItems() + $items_per_page_first - 1 : $this->getTotalItems();
      $pager = $this->pagerManager->createPager($total_items, $this->options['items_per_page'], $this->options['id']);
      // See if the requested page was within range:
      if ($this->getCurrentPage() >= $pager->getTotalPages()) {
        $this->setCurrentPage($pager->getTotalPages() - 1);
      }
    }
  }

  public function getItemsPerPageFirst() {
    return isset($this->options['items_per_page_first_page']) ? $this->options['items_per_page_first_page'] : 0;
  }

}
