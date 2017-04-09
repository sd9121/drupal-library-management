<?php

namespace Drupal\Tests\search_api\Kernel;

use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\entity_test\Entity\EntityTestMulRevChanged;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\search_api\Entity\Index;
use Drupal\Tests\search_api\Functional\ExampleContentTrait;

/**
 * Tests correct functionality of the content entity datasource.
 *
 * @coversDefaultClass \Drupal\search_api\Plugin\search_api\datasource\ContentEntity
 *
 * @group search_api
 */
class ContentEntityDatasourceTest extends KernelTestBase {

  use ExampleContentTrait;

  /**
   * The entity type used in the test.
   *
   * @var string
   */
  protected $testEntityTypeId = 'entity_test_mulrev_changed';

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'search_api',
    'language',
    'user',
    'system',
    'entity_test',
  );

  /**
   * The search index used for testing.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * The datasource used for testing.
   *
   * @var \Drupal\search_api\Plugin\search_api\datasource\EntityDatasourceInterface
   */
  protected $datasource;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Enable translation for the entity_test module.
    \Drupal::state()->set('entity_test.translation', TRUE);

    $this->installSchema('search_api', array('search_api_item'));
    $this->installEntitySchema('entity_test_mulrev_changed');
    $this->installConfig(array('language'));

    // Create some languages.
    for ($i = 0; $i < 2; ++$i) {
      ConfigurableLanguage::create(array(
        'id' => 'l' . $i,
        'label' => 'language - ' . $i,
        'weight' => $i,
      ))->save();
    }

    // Create a test index.
    $this->index = Index::create(array(
      'name' => 'Test Index',
      'id' => 'test_index',
      'status' => FALSE,
      'datasource_settings' => array(
        'entity:' . $this->testEntityTypeId => array(),
      ),
      'tracker_settings' => array(
        'default' => array(),
      ),
    ));
    $this->datasource = $this->index->getDatasource('entity:' . $this->testEntityTypeId);

    $this->setUpExampleStructure();
  }

  /**
   * Tests entity loading.
   *
   * @covers ::loadMultiple
   */
  public function testEntityLoading() {
    foreach (array('item', 'article') as $i => $bundle) {
      $entity = EntityTestMulRevChanged::create(array(
        'id' => $i + 1,
        'type' => $bundle,
        'langcode' => 'l0',
      ));
      $entity->save();
      $entity->addTranslation('l1')->save();
    }

    $all_item_ids = array('1:l0', '1:l1', '2:l0', '2:l1');

    $loaded_items = $this->datasource->loadMultiple($all_item_ids);
    $this->assertCorrectItems($all_item_ids, $loaded_items);

    $this->datasource->setConfiguration(array(
      'bundles' => array(
        'default' => FALSE,
        'selected' => array('item'),
      ),
      'languages' => array(
        'default' => TRUE,
        'selected' => array('l0'),
      ),
    ));
    $loaded_items = $this->datasource->loadMultiple($all_item_ids);
    $this->assertCorrectItems(array('1:l1'), $loaded_items);

    $this->datasource->setConfiguration(array(
      'bundles' => array(
        'default' => TRUE,
        'selected' => array('item'),
      ),
      'languages' => array(
        'default' => FALSE,
        'selected' => array('l0', 'l1'),
      ),
    ));
    $loaded_items = $this->datasource->loadMultiple($all_item_ids);
    $this->assertCorrectItems(array('2:l0', '2:l1'), $loaded_items);
  }

  /**
   * Asserts that the given array of loaded items is correct.
   *
   * @param string[] $expected_ids
   *   The expected item IDs, sorted.
   * @param \Drupal\Core\TypedData\ComplexDataInterface[] $loaded_items
   *   The loaded items.
   */
  protected function assertCorrectItems(array $expected_ids, array $loaded_items) {
    $loaded_ids = array_keys($loaded_items);
    sort($loaded_ids);
    $this->assertEquals($expected_ids, $loaded_ids);

    foreach ($loaded_items as $item_id => $item) {
      $this->assertInstanceOf(EntityAdapter::class, $item);
      $entity = $item->getValue();
      $this->assertInstanceOf(EntityTestMulRevChanged::class, $entity);
      list($id, $langcode) = explode(':', $item_id);
      $this->assertEquals($id, $entity->id());
      $this->assertEquals($langcode, $entity->language()->getId());
    }
  }

}
