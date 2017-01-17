<?php

namespace Drupal\facets\Tests;

use Drupal\Core\Url;
use Drupal\facets\Entity\Facet;
use Drupal\views\Entity\View;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the overall functionality of the Facets admin UI.
 *
 * @group facets
 */
class IntegrationTest extends WebTestBase {

  public static $modules = ['views_ui'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);

    $this->setUpExampleStructure();
    $this->insertExampleContent();
    $this->assertEqual($this->indexItems($this->indexId), 5, '5 items were indexed.');

    // Make absolutely sure the ::$blocks variable doesn't pass information
    // along between tests.
    $this->blocks = NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function installModulesFromClassProperty(ContainerInterface $container) {
    // This will just set the Drupal state to include the necessary bundles for
    // our test entity type. Otherwise, fields from those bundles won't be found
    // and thus removed from the test index. (We can't do it in setUp(), before
    // calling the parent method, since the container isn't set up at that
    // point.)
    $bundles = array(
      'entity_test_mulrev_changed' => array('label' => 'Entity Test Bundle'),
      'item' => array('label' => 'item'),
      'article' => array('label' => 'article'),
    );
    \Drupal::state()->set('entity_test_mulrev_changed.bundles', $bundles);

    parent::installModulesFromClassProperty($container);
  }

  /**
   * Tests permissions.
   */
  public function testOverviewPermissions() {
    $facet_overview = '/admin/config/search/facets';

    // Login with a user that is not authorized to administer facets and test
    // that we're correctly getting a 403 HTTP response code.
    $this->drupalLogin($this->unauthorizedUser);
    $this->drupalGet($facet_overview);
    $this->assertResponse(403);
    $this->assertText('You are not authorized to access this page');

    // Login with a user that has the correct permissions and test for the
    // correct HTTP response code.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
  }

  /**
   * Tests various operations via the Facets' admin UI.
   */
  public function testFramework() {
    $facet_name = "Test Facet name";
    $facet_id = 'test_facet_name';

    // Check if the overview is empty.
    $this->checkEmptyOverview();

    // Add a new facet and edit it. Check adding a duplicate.
    $this->addFacet($facet_name);
    $this->editFacet($facet_name);
    $this->addFacetDuplicate($facet_name);

    // By default, the view should show all entities.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 5 search results', 'The search view displays the correct number of results.');

    // Create and place a block for "Test Facet name" facet.
    $this->blocks[$facet_id] = $this->createBlock($facet_id);

    // Verify that the facet results are correct.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('item');
    $this->assertText('article');

    // Verify that facet blocks appear as expected.
    $this->assertFacetBlocksAppear();

    // Verify that the facet only shows when the facet source is visible.
    $this->setOptionShowOnlyWhenFacetSourceVisible($facet_name);
    $this->goToDeleteFacetPage($facet_name);
    $this->assertNoText('item');
    $this->assertNoText('article');

    // Do not show the block on empty behaviors.
    $this->clearIndex();
    $this->drupalGet('search-api-test-fulltext');

    // Verify that no facet blocks appear. Empty behavior "None" is selected by
    // default.
    $this->assertNoFacetBlocksAppear();

    // Verify that the "empty_text" appears as expected.
    $this->setEmptyBehaviorFacetText($facet_name);
    $this->drupalGet('search-api-test-fulltext');
    $this->assertRaw('block-test-facet-name');
    $this->assertRaw('No results found for this block!');

    // Delete the block.
    $this->deleteBlock($facet_id);

    // Delete the facet and make sure the overview is empty again.
    $this->deleteUnusedFacet($facet_name);
    $this->checkEmptyOverview();
  }

  /**
   * Tests that a block view also works.
   */
  public function testBlockView() {
    $facet_name = "Block view facet";
    $facet_id = 'bvf';

    $this->createFacet($facet_name, $facet_id, 'type', 'block_1');
    $this->drupalPostForm(NULL, ['facet_settings[only_visible_when_facet_source_is_visible]' => FALSE], 'Save');

    // Place the views block in the footer of all pages.
    $block_settings = [
      'region' => 'sidebar_first',
      'id' => 'view_block',
    ];
    $this->drupalPlaceBlock('views_block:search_api_test_view-block_1', $block_settings);

    // By default, the view should show all entities.
    $this->drupalGet('<front>');
    $this->assertText('Fulltext test index', 'The search view is shown on the page.');
    $this->assertText('Displaying 5 search results', 'The search view displays the correct number of results.');
    $this->assertText('item');
    $this->assertText('article');

    // Click the item link, and test that filtering of results actually works.
    $this->clickLink('item');
    $this->assertText('Displaying 3 search results', 'The search view displays the correct number of results.');
  }

  /**
   * Tests for deleting a block.
   */
  public function testBlockDelete() {
    $name = 'Tawny-browed owl';
    $id = 'tawny_browed_owl';

    // Add a new facet.
    $this->createFacet($name, $id);

    $block = $this->blocks[$id];
    $block_id = $block->label();

    $this->drupalGet('admin/structure/block');
    $this->assertText($block_id);

    $this->drupalGet('admin/structure/block/library/classy');
    $this->assertText($name);

    $this->drupalGet('admin/config/search/facets/' . $id . '/delete');
    $this->drupalPostForm(NULL, [], $this->t('Delete'));

    $this->drupalGet('admin/structure/block/library/classy');
    $this->assertNoText($name);
  }

  /**
   * Tests that an url alias works correctly.
   */
  public function testUrlAlias() {
    $facet_id = 'ab_facet';
    $facet_name = 'ab>Facet';
    $facet_edit_page = '/admin/config/search/facets/' . $facet_id . '/edit';
    $this->createFacet($facet_name, $facet_id);

    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    $this->clickLink('item');
    $url = Url::fromUserInput('/search-api-test-fulltext', ['query' => ['f[0]' => 'ab_facet:item']]);
    $this->assertUrl($url);

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['facet_settings[url_alias]' => 'llama'], $this->t('Save'));

    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    $this->clickLink('item');
    $url = Url::fromUserInput('/search-api-test-fulltext', ['query' => ['f[0]' => 'llama:item']]);
    $this->assertUrl($url);
  }

  /**
   * Tests facet dependencies.
   */
  public function testFacetDependencies() {
    $facet_name = "DependableFacet";
    $facet_id = 'dependablefacet';

    $depending_facet_name = "DependingFacet";
    $depending_facet_id = "dependingfacet";

    $this->addFacet($facet_name);
    $this->addFacet($depending_facet_name, 'keywords');

    // Create both facets as blocks and add them on the page.
    $this->blocks[$facet_id] = $this->createBlock($facet_id);
    $this->blocks[$depending_facet_id] = $this->createBlock($depending_facet_id);

    // Go the the view and test that both facets are shown. Item and article
    // come from the DependableFacet, orange and grape come from DependingFacet.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('grape');
    $this->assertFacetLabel('orange');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');
    $this->assertFacetBlocksAppear();

    // Change the visiblity settings of the DependingFacet.
    $this->drupalGet('admin/structure/block/manage/dependingfacet');
    $edit = [
      'visibility[other_facet][facets]' => 'facet_block:dependablefacet',
      'visibility[other_facet][facet_value]' => 'item',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save block'));
    $this->assertText('The block configuration has been saved.');

    // Go to the view and test that only the types are shown.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertNoLink('grape');
    $this->assertNoLink('orange');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    // Click on the item, and test that this shows the keywords.
    $this->clickLink('item');
    $this->assertFacetLabel('grape');
    $this->assertFacetLabel('orange');

    // Go back to the view, click on article and test that the keywords are
    // hidden.
    $this->drupalGet('search-api-test-fulltext');
    $this->clickLink('article');
    $this->assertNoLink('grape');
    $this->assertNoLink('orange');

    // Change the visibility settings to negate the previous settings.
    $this->drupalGet('admin/structure/block/manage/dependingfacet');
    $edit = [
      'visibility[other_facet][facets]' => 'facet_block:dependablefacet',
      'visibility[other_facet][facet_value]' => 'item',
      'visibility[other_facet][negate]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save block'));

    // Go the the view and test only the type facet is shown.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');
    $this->assertFacetLabel('grape');
    $this->assertFacetLabel('orange');

    // Click on the article, and test that this shows the keywords.
    $this->clickLink('article');
    $this->assertFacetLabel('grape');
    $this->assertFacetLabel('orange');

    // Go back to the view, click on item and test that the keywords are
    // hidden.
    $this->drupalGet('search-api-test-fulltext');
    $this->clickLink('item');
    $this->assertNoLink('grape');
    $this->assertNoLink('orange');
  }

  /**
   * Tests the facet's and/or functionality.
   */
  public function testAndOrFacet() {
    $facet_name = 'test & facet';
    $facet_id = 'test_facet';
    $facet_edit_page = 'admin/config/search/facets/' . $facet_id . '/edit';

    $this->createFacet($facet_name, $facet_id);

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['facet_settings[query_operator]' => 'and'], $this->t('Save'));

    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    $this->clickLink('item');
    $this->checkFacetIsActive('item');
    $this->assertNoLink('article');

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['facet_settings[query_operator]' => 'or'], $this->t('Save'));
    $this->drupalGet('search-api-test-fulltext');
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    $this->clickLink('item');
    $this->checkFacetIsActive('item');
    $this->assertFacetLabel('article');

    // Verify the number of results for OR functionality.
    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['widget' => 'links', 'widget_config[show_numbers]' => TRUE], $this->t('Save'));
    $this->drupalGet('search-api-test-fulltext');
    $this->clickLink('item (3)');
    $this->assertFacetLabel('article (2)');

  }

  /**
   * Tests that we disallow unwanted values when creating a facet trough the UI.
   */
  public function testUnwantedValues() {
    // Go to the Add facet page and make sure that returns a 200.
    $facet_add_page = '/admin/config/search/facets/add-facet';
    $this->drupalGet($facet_add_page);
    $this->assertResponse(200);

    // Configure the facet source by selecting one of the Search API views.
    $this->drupalGet($facet_add_page);
    $this->drupalPostForm(NULL, ['facet_source_id' => 'views_page:search_api_test_view__page_1'], $this->t('Configure facet source'));

    // Fill in all fields and make sure the 'field is required' message is no
    // longer shown.
    $facet_source_form = [
      'facet_source_id' => 'views_page:search_api_test_view__page_1',
      'facet_source_configs[views_page:search_api_test_view__page_1][field_identifier]' => 'type',
    ];
    $this->drupalPostForm(NULL, $facet_source_form, $this->t('Save'));

    $form_values = [
      'name' => 'name 1',
      'id' => 'name 1',
    ];
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertText($this->t('The machine-readable name must contain only lowercase letters, numbers, and underscores.'));

    $form_values = [
      'name' => 'name 1',
      'id' => 'name:&1',
    ];
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertText($this->t('The machine-readable name must contain only lowercase letters, numbers, and underscores.'));

    // Post the form with valid values, so we can test the next step.
    $form_values = [
      'name' => 'name 1',
      'id' => 'name_1',
    ];
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));

    // Create an array of values that are not allowed in the url.
    $unwanted_values = [' ', '!', '@', '#', '$', '%', '^', '&'];
    foreach ($unwanted_values as $unwanted_value) {
      $form_values = [
        'facet_settings[url_alias]' => 'alias' . $unwanted_value . '1',
      ];
      $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
      $this->assertText($this->t('Url alias has illegal characters.'));
    }

    // Post an alias with allowed values.
    $form_values = [
      'facet_settings[url_alias]' => 'alias~-_.1',
    ];
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertRaw($this->t('Facet %name has been updated.', ['%name' => 'name 1']));
  }

  /**
   * Tests the facet's exclude functionality.
   */
  public function testExcludeFacet() {
    $facet_name = 'test & facet';
    $facet_id = 'test_facet';
    $facet_edit_page = 'admin/config/search/facets/' . $facet_id . '/edit';
    $this->createFacet($facet_name, $facet_id);

    $this->drupalGet($facet_edit_page);
    $this->assertNoFieldChecked('edit-facet-settings-exclude');
    $this->drupalPostForm(NULL, ['facet_settings[exclude]' => TRUE], $this->t('Save'));
    $this->assertResponse(200);
    $this->assertFieldChecked('edit-facet-settings-exclude');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('foo bar baz');
    $this->assertText('foo baz');
    $this->assertFacetLabel('item');

    $this->clickLink('item');
    $this->checkFacetIsActive('item');
    $this->assertText('foo baz');
    $this->assertText('bar baz');
    $this->assertNoText('foo bar baz');

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['facet_settings[exclude]' => FALSE], $this->t('Save'));
    $this->assertResponse(200);
    $this->assertNoFieldChecked('edit-facet-settings-exclude');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('foo bar baz');
    $this->assertText('foo baz');
    $this->assertFacetLabel('item');

    $this->clickLink('item');
    $this->checkFacetIsActive('item');
    $this->assertText('foo bar baz');
    $this->assertText('foo test');
    $this->assertText('bar');
    $this->assertNoText('foo baz');
  }

  /**
   * Tests allow only one active item.
   */
  public function testAllowOneActiveItem() {
    $facet_name = 'Spotted wood owl';
    $facet_id = 'spotted_wood_owl';
    $facet_edit_page = 'admin/config/search/facets/' . $facet_id;

    $this->createFacet($facet_name, $facet_id, 'keywords');

    $this->drupalGet($facet_edit_page . '/edit');
    $edit = ['facet_settings[show_only_one_result]' => TRUE];
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 5 search results');
    $this->assertFacetLabel('grape');
    $this->assertFacetLabel('orange');

    $this->clickLink('grape');
    $this->assertText('Displaying 3 search results');
    $this->checkFacetIsActive('grape');
    $this->assertFacetLabel('orange');

    $this->clickLink('orange');
    $this->assertText('Displaying 3 search results');
    $this->assertFacetLabel('grape');
    $this->checkFacetIsActive('orange');
  }

  /**
   * Tests facet weights.
   */
  public function testWeight() {
    $facet_name = "Forest owlet";
    $id = "forest_owlet";
    $this->createFacet($facet_name, $id);

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = Facet::load($id);
    $facet->setWeight(10);
    $this->assertEqual(10, $facet->getWeight());
  }

  /**
   * Tests calculations of facet count.
   */
  public function testFacetCountCalculations() {
    $this->addFacet('Type');
    $this->addFacet('Keywords', 'keywords');
    $this->createBlock('type');
    $this->createBlock('keywords');

    $edit = [
      'widget' => 'links',
      'widget_config[show_numbers]' => '1',
      'facet_settings[query_operator]' => 'and',
    ];
    $this->drupalPostForm('admin/config/search/facets/keywords/edit', $edit, $this->t('Save'));
    $this->drupalPostForm('admin/config/search/facets/type/edit', $edit, $this->t('Save'));

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 5 search results');
    $this->assertFacetLabel('article (2)');
    $this->assertFacetLabel('grape (3)');

    // Make sure that after clicking on article, which has only 2 entities,
    // there are only 2 items left in the results for other facets as well.
    // In this case, that means we can't have 3 entities tagged with grape. Both
    // remaining entities are tagged with grape and strawberry.
    $this->clickLinkPartialName('article');
    $this->assertNoText('(3)');
    $this->assertFacetLabel('grape (2)');
    $this->assertFacetLabel('strawberry (2)');

    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('Displaying 5 search results');
    $this->assertFacetLabel('article (2)');
    $this->assertFacetLabel('grape (3)');

    // Make sure that after clicking on grape, which has only 3 entities, there
    // are only 3 items left in the results for other facets as well. In this
    // case, that means 2 entities of type article and 1 item.
    $this->clickLinkPartialName('grape');
    $this->assertText('Displaying 3 search results');
    $this->assertFacetLabel('article (2)');
    $this->assertFacetLabel('item (1)');
  }

  /**
   * Tests what happens when a dependency is removed.
   */
  public function testOnViewRemoval() {
    $id = "owl";
    $name = "Owl";
    $this->createFacet($name, $id);

    $this->drupalGet('/admin/config/search/facets');
    $this->assertResponse(200);

    // Check that the expected facet sources and the owl facet are shown.
    $this->assertText('views_page:search_api_test_view__block_1');
    $this->assertText('views_page:search_api_test_view__page_1');
    $this->assertText($name);

    // Delete the view on which both facet sources are based.
    $view = View::load('search_api_test_view');
    $view->delete();

    // Go back to the overview, make sure that the page doesn't show any errors
    // and the facet/facet source are deleted.
    $this->drupalGet('/admin/config/search/facets');
    $this->assertResponse(200);
    $this->assertNoText('views_page:search_api_test_view__page_1');
    $this->assertNoText('views_page:search_api_test_view__block_1');
    $this->assertNoText($name);
  }

  /**
   * Tests what happens when a dependency is removed.
   */
  public function testOnViewDisplayRemoval() {
    $admin_user = $this->drupalCreateUser([
      'administer search_api',
      'administer facets',
      'access administration pages',
      'administer nodes',
      'access content overview',
      'administer content types',
      'administer blocks',
      'administer views',
    ]);
    $this->drupalLogin($admin_user);
    $id = "owl";
    $name = "Owl";
    $this->createFacet($name, $id);

    $this->drupalGet('/admin/config/search/facets');
    $this->assertResponse(200);

    // Check that the expected facet sources and the owl facet are shown.
    $this->assertText('views_page:search_api_test_view__block_1');
    $this->assertText('views_page:search_api_test_view__page_1');
    $this->assertText($name);

    // Delete the view display for the page.
    $this->drupalGet('admin/structure/views/view/search_api_test_view');
    $this->drupalPostForm(NULL, [], $this->t('Delete Page'));
    $this->drupalPostForm(NULL, [], $this->t('Save'));

    // Go back to the overview, make sure that the page doesn't show any errors
    // and the facet/facet source are deleted.
    $this->drupalGet('/admin/config/search/facets');
    $this->assertResponse(200);
    $this->assertNoText('views_page:search_api_test_view__page_1');
    $this->assertText('views_page:search_api_test_view__block_1');
    $this->assertNoText($name);
  }

  /**
   * Configures empty behavior option to show a text on empty results.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function setEmptyBehaviorFacetText($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_display_page = '/admin/config/search/facets/' . $facet_id . '/edit';

    // Go to the facet edit page and make sure "edit facet %facet" is present.
    $this->drupalGet($facet_display_page);
    $this->assertResponse(200);

    // Configure the text for empty results behavior.
    $edit = [
      'facet_settings[empty_behavior]' => 'text',
      'facet_settings[empty_behavior_container][empty_behavior_text][value]' => 'No results found for this block!',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

  }

  /**
   * Configures a facet to only be visible when accessing to the facet source.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function setOptionShowOnlyWhenFacetSourceVisible($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_edit_page = '/admin/config/search/facets/' . $facet_id . '/edit';
    $this->drupalGet($facet_edit_page);
    $this->assertResponse(200);

    $edit = [
      'facet_settings[only_visible_when_facet_source_is_visible]' => TRUE,
      'widget' => 'links',
      'widget_config[show_numbers]' => '0',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
  }

  /**
   * Tests that the facet overview is empty.
   */
  protected function checkEmptyOverview() {
    $facet_overview = '/admin/config/search/facets';
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);

    // The list overview has Field: field_name as description. This tests on the
    // absence of that.
    $this->assertNoText('Field:');

    // Check that the expected facet sources are shown.
    $this->assertText('views_page:search_api_test_view__block_1');
    $this->assertText('views_page:search_api_test_view__page_1');
  }

  /**
   * Tests adding a facet trough the interface.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function addFacet($facet_name, $facet_type = 'type') {
    $facet_id = $this->convertNameToMachineName($facet_name);

    // Go to the Add facet page and make sure that returns a 200.
    $facet_add_page = '/admin/config/search/facets/add-facet';
    $this->drupalGet($facet_add_page);
    $this->assertResponse(200);

    $form_values = [
      'name' => '',
      'id' => $facet_id,
    ];

    // Try filling out the form, but without having filled in a name for the
    // facet to test for form errors.
    $this->drupalPostForm($facet_add_page, $form_values, $this->t('Save'));
    $this->assertText($this->t('Name field is required.'));
    $this->assertText($this->t('Facet source field is required.'));

    // Make sure that when filling out the name, the form error disappears.
    $form_values['name'] = $facet_name;
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertNoText($this->t('Name field is required.'));

    // Configure the facet source by selecting one of the Search API views.
    $this->drupalGet($facet_add_page);
    $this->drupalPostForm(NULL, ['facet_source_id' => 'views_page:search_api_test_view__page_1'], $this->t('Configure facet source'));

    // The field is still required.
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertText($this->t('Field field is required.'));

    // Fill in all fields and make sure the 'field is required' message is no
    // longer shown.
    $facet_source_form = [
      'facet_source_id' => 'views_page:search_api_test_view__page_1',
      'facet_source_configs[views_page:search_api_test_view__page_1][field_identifier]' => $facet_type,
    ];
    $this->drupalPostForm(NULL, $form_values + $facet_source_form, $this->t('Save'));
    $this->assertNoText('field is required.');

    // Make sure that the redirection to the display page is correct.
    $this->assertRaw(t('Facet %name has been created.', ['%name' => $facet_name]));
    $this->assertUrl('admin/config/search/facets/' . $facet_id . '/edit');

    $this->drupalGet('admin/config/search/facets');
  }

  /**
   * Tests creating a facet with an existing machine name.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function addFacetDuplicate($facet_name, $facet_type = 'type') {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_add_page = '/admin/config/search/facets/add-facet';
    $this->drupalGet($facet_add_page);

    $form_values = [
      'name' => $facet_name,
      'id' => $facet_id,
      'facet_source_id' => 'views_page:search_api_test_view__page_1',
    ];

    $facet_source_configs['facet_source_configs[views_page:search_api_test_view__page_1][field_identifier]'] = $facet_type;

    // Try to submit a facet with a duplicate machine name after form rebuilding
    // via facet source submit.
    $this->drupalPostForm(NULL, $form_values, $this->t('Configure facet source'));
    $this->drupalPostForm(NULL, $form_values + $facet_source_configs, $this->t('Save'));
    $this->assertText($this->t('The machine-readable name is already in use. It must be unique.'));

    // Try to submit a facet with a duplicate machine name after form rebuilding
    // via facet source submit using AJAX.
    $this->drupalPostAjaxForm(NULL, $form_values, array('facet_source_configure' => t('Configure facet source')));
    $this->drupalPostForm(NULL, $form_values + $facet_source_configs, $this->t('Save'));
    $this->assertText($this->t('The machine-readable name is already in use. It must be unique.'));
  }

  /**
   * Tests editing of a facet through the UI.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function editFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_edit_page = '/admin/config/search/facets/' . $facet_id . '/settings';

    // Go to the facet edit page and make sure "edit facet %facet" is present.
    $this->drupalGet($facet_edit_page);
    $this->assertResponse(200);
    $this->assertRaw($this->t('Facet settings for @facet facet', ['@facet' => $facet_name]));

    // Check if it's possible to change the machine name.
    $elements = $this->xpath('//form[@id="facets-facet-settings-form"]/div[contains(@class, "form-item-id")]/input[@disabled]');
    $this->assertEqual(count($elements), 1, 'Machine name cannot be changed.');

    // Change the facet name to add in "-2" to test editing of a facet works.
    $form_values = ['name' => $facet_name . ' - 2'];
    $this->drupalPostForm($facet_edit_page, $form_values, $this->t('Save'));

    // Make sure that the redirection back to the overview was successful and
    // the edited facet is shown on the overview page.
    $this->assertRaw(t('Facet %name has been updated.', ['%name' => $facet_name . ' - 2']));

    // Make sure the "-2" suffix is still on the facet when editing a facet.
    $this->drupalGet($facet_edit_page);
    $this->assertRaw($this->t('Facet settings for @facet facet', ['@facet' => $facet_name . ' - 2']));

    // Edit the form and change the facet's name back to the initial name.
    $form_values = ['name' => $facet_name];
    $this->drupalPostForm($facet_edit_page, $form_values, $this->t('Save'));

    // Make sure that the redirection back to the overview was successful and
    // the edited facet is shown on the overview page.
    $this->assertRaw(t('Facet %name has been updated.', ['%name' => $facet_name]));
  }

  /**
   * Deletes a facet through the UI that still has usages.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function deleteUsedFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_delete_page = '/admin/config/search/facets/' . $facet_id . '/delete';

    // Go to the facet delete page and make the warning is shown.
    $this->drupalGet($facet_delete_page);
    $this->assertResponse(200);

    // Check that the facet by testing for the message and the absence of the
    // facet name on the overview.
    $this->assertRaw($this->t("The facet is currently used in a block and thus can't be removed. Remove the block first."));
  }

  /**
   * Deletes a facet through the UI.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function deleteUnusedFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_delete_page = '/admin/config/search/facets/' . $facet_id . '/delete';
    $facet_overview = '/admin/config/search/facets';

    // Go to the facet delete page and make the warning is shown.
    $this->drupalGet($facet_delete_page);
    $this->assertResponse(200);
    $this->assertText("This action cannot be undone.");

    // Click the cancel link and see that we redirect to the overview page.
    $this->clickLink("Cancel");
    $this->assertUrl($facet_overview);

    // Back to the delete page.
    $this->drupalGet($facet_delete_page);

    // Actually submit the confirmation form.
    $this->drupalPostForm(NULL, [], $this->t('Delete'));

    // Check that the facet by testing for the message and the absence of the
    // facet name on the overview.
    $this->assertRaw($this->t('The facet %facet has been deleted.', ['%facet' => $facet_name]));

    // Refresh the page because on the previous page the $facet_name is still
    // visible (in the message).
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
    $this->assertNoText($facet_name);
  }

  /**
   * Add fields to Search API index.
   */
  protected function addFieldsToIndex() {
    $edit = [
      'fields[entity:node/nid][indexed]' => 1,
      'fields[entity:node/title][indexed]' => 1,
      'fields[entity:node/title][type]' => 'text',
      'fields[entity:node/title][boost]' => '21.0',
      'fields[entity:node/body][indexed]' => 1,
      'fields[entity:node/uid][indexed]' => 1,
      'fields[entity:node/uid][type]' => 'search_api_test_data_type',
    ];

    $this->drupalPostForm('admin/config/search/search-api/index/webtest_index/fields', $edit, $this->t('Save changes'));
    $this->assertText($this->t('The changes were successfully saved.'));
  }

  /**
   * Convert facet name to machine name.
   *
   * @param string $facet_name
   *   The name of the facet.
   *
   * @return string
   *   The facet name changed to a machine name.
   */
  protected function convertNameToMachineName($facet_name) {
    return preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));
  }

  /**
   * Go to the Delete Facet Page using the facet name.
   *
   * @param string $facet_name
   *   The name of the facet.
   */
  protected function goToDeleteFacetPage($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_delete_page = '/admin/config/search/facets/' . $facet_id . '/delete';

    // Go to the facet delete page and make the warning is shown.
    $this->drupalGet($facet_delete_page);
    $this->assertResponse(200);
  }

}