<?php

namespace Drupal\content_mod_vbo\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 *
 * @Action(
 *   id = "content_mod_vbo",
 *   label = @Translation("Content Moderation Alter"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
class ContentModAction extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface, PluginFormInterface {

  public function execute($entity = NULL) {

    $state = $this->configuration['content_mob_vbo_setting'];
    if(empty($this->configuration['content_mob_vbo_setting'])) {
      $state = "published";
    }
    $entity->set('moderation_state',$state);
    if ($entity instanceof RevisionLogInterface) {
      $entity->setRevisionLogMessage('VBO State Change');
      $entity->setRevisionUserId($this->currentUser()->id());
    }
    $entity->save();

    return sprintf('Moderation State Changed to '.$state);
  }

  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
    $form['content_mob_vbo_preconfig_setting'] = [
      '#title' => $this->t('Moderation State'),
      '#type' => 'textfield',
      '#default_value' => isset($values['content_mob_vbo_preconfig_setting']) ? $values['content_mob_vbo_preconfig_setting'] : '',
    ];
    return $form;
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['content_mob_vbo_setting'] = [
      '#title' => t('Content Moderation setting pre-execute'),
      '#type' => 'textfield',
      '#default_value' => $form_state->getValue('content_mob_vbo_setting'),
    ];
    return $form;
  }

  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['content_mob_vbo_setting'] = $form_state->getValue('content_mob_vbo_setting');
  }

  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }
    return TRUE;
  }

}
