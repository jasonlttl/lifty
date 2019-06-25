<?php

use Acquia\LiftClient\Lift;

class RoboFile extends \Robo\Tasks
{

  /**
   * Gets list of rules by content id.
   *
   * @return void
   */
  public function rulesByContent($contentId)
  {
    $filtered = [];
    $siteSlots = $this->getSlots();
    $siteRules = $this->getRules();
    foreach ($siteRules as $site => $rules) {
      foreach ($rules as $id => $rule) {
        foreach ($rule->getContentList() as $content) {
          if ($content['id'] == $contentId) {
            $slot = $siteSlots[$site][$rule->getSlotId()];
            $filtered[$site][$slot->getLabel()] = $slot->getVisibility();
          }
        }
      }
    }

    print_r($filtered);
  }
  /**
   * Prints a list of rules.
   *
   * @return void
   */
  public function rules() {
    $rules = [];
    $clients = $this->getSiteClients();
    foreach ($clients as $site => $client) {
      $manager = $client->getRuleManager();
      $results = $manager->query(['prefetch' => true]);
      foreach ($results as $rule) {
        $rules[$site][$rule->getId()] = [
          'title' => $rule->getLabel(),
          'description' => $rule->getDescription(),
          'segment' => $rule->getSegmentId(),
          'weight' => $rule->getWeight(),
          'status' => $rule->getStatus(),
          'created' => $rule->getCreated(),
          'updated' => $rule->getUpdated(),
        ];
      }
    }
    ksort($rules);
    $this->say(print_r($rules, true));
  }

  public function slots() {
    $slots = [];
    $clients = $this->getSiteClients();
    foreach ($clients as $site => $client) {
      $manager = $client->getSlotManager();
      $results = $manager->query(['prefetch' => true]);
      foreach ($results as $slot) {
        $slots[$site][$slot->getId()] = $slot;
      }
    }
    ksort($slots);
    $this->say(print_r($slots, true));
  }

  private function getRules() {
    $rules = [];
    $clients = $this->getSiteClients();
    foreach ($clients as $site => $client) {
      $manager = $client->getRuleManager();
      $results = $manager->query(['prefetch' => true]);
      foreach ($results as $rule) {
        $rules[$site][$rule->getId()] = [$rule];
      }
    }
    ksort($rules);
    return $rules;
  }

  private function getSlots() {
    $slots = [];
    $clients = $this->getSiteClients();
    foreach ($clients as $site => $client) {
      $manager = $client->getSlotManager();
      $results = $manager->query(['prefetch' => true]);
      foreach ($results as $slot) {
        $slots[$site][$slot->getId()] = $slot;
      }
    }
    ksort($slots);
    return $slots;
  }

  public function segmentsAll() {
    $client = $this->getClient();
    $manager = $client->getSegmentManager();
    $segments = $manager->query();
    usort($segments, function($a, $b) {
      return strcmp($a->getName(), $b->getName());
    });
    foreach ($segments as $segment) {
      $this->say($segment->getName());
    }
  }

  private function getSiteClients() {
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    $clients = [];
    $sites = explode(',', getenv('ACQUIA_LIFT_SITE_IDS'));
    foreach ($sites as $site) {
      $clients[$site] = new Lift(
        getenv('ACQUIA_LIFT_ACCOUNT_ID'),
        $site,
        getenv('ACQUIA_LIFT_API_KEY'),
        getenv('ACQUIA_LIFT_SECRET_KEY'),
       ['base_url' => getenv('ACQUIA_LIFT_URL')]
      );
    }
    return $clients;
  }

  private function getClient() {
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    $sites = explode(',', getenv('ACQUIA_LIFT_SITE_IDS'));

    return new Lift(
      getenv('ACQUIA_LIFT_ACCOUNT_ID'),
      $sites[0],
      getenv('ACQUIA_LIFT_API_KEY'),
      getenv('ACQUIA_LIFT_SECRET_KEY'),
      ['base_url' => getenv('ACQUIA_LIFT_URL')]
    );

  }
}
