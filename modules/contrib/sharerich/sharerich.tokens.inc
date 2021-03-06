<?php
/**
 * @file
 * Tokens for Sharerich.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;

/**
 * Implements hook_token_info().
 */
function sharerich_token_info() {
  $type = array(
    'name' => t('Sharerich Tokens'),
    'description' => t('Tokens used to create the share buttons.'),
  );

  $token['fb_app_id'] = array(
    'name' => t('Facebook App Id'),
    'description' => t("The App Id used to display the share box."),
  );
  $token['fb_site_url'] = array(
    'name' => t("Facebook site Url"),
    'description' => t("The Url used to display the share box."),
  );
  $token['youtube_username'] = array(
    'name' => t("Youtube Username"),
    'description' => t("Used to display the share box."),
  );
  $token['github_username'] = array(
    'name' => t("Github Username"),
    'description' => t("Used to display the share box."),
  );
  $token['instagram_username'] = array(
    'name' => t("Instagram Username"),
    'description' => t("Used to display the share box."),
  );
  $token['twitter_user'] = array(
    'name' => t("Twitter ID"),
    'description' => t("Used in Twitter button via @userid."),
  );

  return array(
    'types' => array('sharerich' => $type),
    'tokens' => array('sharerich' => $token),
  );
}

/**
 * Implements hook_tokens().
 *
 * @todo Support Taxonomy and User tokens.
 */
function sharerich_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = array();

  if ($type == 'sharerich') {
    foreach ($tokens as $name => $original) {
      $value = '';
      switch ($name) {
        case 'fb_app_id':
          $value = \Drupal\Component\Utility\Html::escape(\Drupal::config('sharerich.settings')
            ->get('facebook_app_id'));
          break;

        case 'fb_site_url':
          $value = \Drupal\Component\Utility\Html::escape(\Drupal::config('sharerich.settings')
            ->get('facebook_site_url'));
          break;

        case 'youtube_username':
          $value = \Drupal\Component\Utility\Html::escape(\Drupal::config('sharerich.settings')
            ->get('youtube_username'));
          break;

        case 'github_username':
          $value = \Drupal\Component\Utility\Html::escape(\Drupal::config('sharerich.settings')
            ->get('github_username'));
          break;

        case 'instagram_username':
          $value = \Drupal\Component\Utility\Html::escape(\Drupal::config('sharerich.settings')
            ->get('instagram_username'));
          break;

        case 'twitter_user':
          $value = \Drupal\Component\Utility\Html::escape(\Drupal::config('sharerich.settings')
            ->get('twitter_user'));
          break;

        case 'url':
          if (isset($data['node']) && !empty($data['node'])) {
            $value = \Drupal::token()->replace('[node:url]', $data, $options);
          }
          else {
            $value = \Drupal::token()->replace('[current-page:url]');
          }
          $value = rawurlencode($value);
          break;

        case 'title':
          if (isset($data['node']) && !empty($data['node'])) {
            $value = \Drupal::token()->replace('[node:title]', $data, $options);
          }
          else {
            $value = \Drupal::token()->replace('[current-page:title]');
          }
          $value = rawurlencode(\Drupal\Component\Utility\Html::decodeEntities($value));
          break;

        case 'summary':
          if (isset($data['node']) && !empty($data['node'])) {
            $value = \Drupal::token()->replace('[node:summary]', $data, $options);
          }
          else {
            $value = \Drupal::token()->replace('[site:name]');
          }
          $value = rawurlencode(\Drupal\Component\Utility\Html::decodeEntities($value));
          break;
      }
      $replacements[$original] = $value;
    }

    return $replacements;
  }
}
