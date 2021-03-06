<?php

namespace Drupal\ds\Plugin\DsField;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Interface for DS plugins.
 */
interface DsFieldInterface extends ConfigurablePluginInterface, ContainerFactoryPluginInterface {

  /**
   * Renders a field.
   */
  public function build();

  /**
   * Returns the summary of the chosen settings.
   *
   * @param $settings
   *   Contains the settings of the field.
   *
   * @return array
   *   A render array containing the summary.
   */
  public function settingsSummary($settings);

  /**
   * The form that holds the settings for this plugin.
   */
  public function settingsForm($form, FormStateInterface $form_state);

  /**
   * Returns a list of possible formatters for this field.
   *
   * @return array
   *   A list of possible formatters.
   */
  public function formatters();

  /**
   * Returns if the field is allowed on the field UI screen.
   */
  public function isAllowed();

  /**
   * Gets the current entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The current entity.
   */
  public function entity();

  /**
   * Gets the current entity type.
   */
  public function getEntityTypeId();

  /**
   * Gets the current bundle.
   */
  public function bundle();

  /**
   * Gets the view mode.
   */
  public function viewMode();

  /**
   * Gets the field configuration.
   */
  public function getFieldConfiguration();

  /**
   * Gets the field name.
   */
  public function getName();

  /**
   * Returns the title of the field.
   */
  public function getTitle();

  /**
   * Defines if we are dealing with a multivalue field.
   */
  public function isMultiple();

}
