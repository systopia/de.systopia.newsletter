<?php
declare(strict_types = 1);

namespace Civi\Newsletter;

class ContactChecksumService {

  private static self $instance;

  public static function getInstance(): self {
    return self::$instance ??= new self();
  }

  /**
   * Generates a timely limited contact checksum that can be resolved back to
   * the contact ID. The checksum expires after the number of days configured in
   * the Civi setting "checksum_timeout".
   *
   * @throws \CRM_Core_Exception
   *
   * @see resolveChecksum()
   */
  public function generateChecksum(int $contact_id): string {
    return $contact_id . '_' . \CRM_Contact_BAO_Contact_Utils::generateChecksum($contact_id);
  }

  /**
   * Verifies a contact checksum and resolves it to the contact ID.
   *
   * @return int
   *   The resolved contact ID or NULL if the checksum isn't valid.
   *
   * @throws \CRM_Core_Exception
   *
   * @see generateChecksum()
   */
  public function resolveChecksum(string $contact_checksum): ?int {
    if (preg_match('/^(?<contact_id>[1-9][0-9]*)_(?<checksum>[0-9a-z_]+)$/i', $contact_checksum, $match)) {
      $contact_id = (int) $match['contact_id'];
      if (\CRM_Contact_BAO_Contact_Utils::validChecksum($contact_id, $match['checksum'])) {
        return $contact_id;
      }
    }

    return NULL;
  }

}
