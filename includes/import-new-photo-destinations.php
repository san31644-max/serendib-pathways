<?php
// Temporary fixed-data deployment migration; removed after live verification.
// No request parameters or externally supplied SQL are accepted.
if ($db instanceof PDO) {
    $locked = false;
    try {
        $locked = (bool) $db->query("SELECT GET_LOCK('serendib_photo_import_20260905', 5)")->fetchColumn();
        if ($locked) {
            $sql = file_get_contents(__DIR__ . '/../config/new-photo-destinations.sql');
            $db->beginTransaction();
            foreach (preg_split('/;\s*(?:\r?\n|$)/', trim($sql)) as $statement) {
                $statement = trim($statement);
                if ($statement === '' || preg_match('/^(USE\s|START TRANSACTION$|COMMIT$)/i', $statement)) {
                    continue;
                }
                if ($db->exec($statement) === false) {
                    throw new RuntimeException('Photo destination import failed.');
                }
            }
            $db->commit();
        }
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('Photo destination import failed: ' . $error->getMessage());
    } finally {
        if ($locked) {
            $db->query("SELECT RELEASE_LOCK('serendib_photo_import_20260905')");
        }
    }
}
