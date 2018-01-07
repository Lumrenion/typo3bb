DELIMITER //
-- Method checks if a csv has any value in another csv. With | as separator in andHaystack, multiple csv can be AND stacked
DROP FUNCTION IF EXISTS `findMultipleInAndSet` //
CREATE FUNCTION `findMultipleInAndSet` (needles TEXT, andHaystack TEXT) RETURNS BOOLEAN
DETERMINISTIC
  BEGIN
    DECLARE singleStack TEXT DEFAULT NULL;
    DECLARE singleStackLen INT DEFAULT NULL;
    DECLARE result INT DEFAULT 0;
    iterator:
    LOOP
      IF LENGTH(TRIM(andHaystack)) = 0 OR andHaystack IS NULL THEN LEAVE iterator; END IF;

      SET singleStack = SUBSTRING_INDEX(andHaystack, '|', 1);
      SET singleStackLen = LENGTH(singleStack);

      SELECT CONCAT(",", singleStack, ",") REGEXP CONCAT(",(", needles, "),") INTO result;
      IF (result = 0) THEN RETURN FALSE; END IF;

      SET andHaystack = INSERT(andHaystack, 1, singleStackLen + 1, '');
    END LOOP;

    RETURN TRUE;
  END //
-- determines read permissions of a board recursively, separating each board by | and makes the string compatible with findMultipleInAndStack-Function
-- This is a procedure because functions don't allow recursion
DROP PROCEDURE IF EXISTS getReadpermissionsRecursive //
CREATE PROCEDURE getReadpermissionsRecursive(
  IN board INT,
  INOUT returnValue TEXT
)
  BEGIN
    DECLARE parentBoard INT DEFAULT 0 ;
    DECLARE readPermissions TEXT DEFAULT '';
    SET max_sp_recursion_depth = 255;

    SELECT parent_board   FROM tx_typo3bb_domain_model_board   WHERE uid = board INTO parentBoard;
    SELECT read_permissions   FROM tx_typo3bb_domain_model_board   WHERE uid = board INTO readPermissions;

    SET returnValue = TRIM(BOTH '|' FROM CONCAT(COALESCE(returnValue, ''), '|', readPermissions));

    IF parentBoard != 0 THEN
      CALL getReadpermissionsRecursive(parentBoard, returnValue);
    END IF;
  END//
-- Just a function wrapper for getReadpermissionsRecursive procedure
DROP FUNCTION IF EXISTS getReadpermissionsRecursive //
CREATE FUNCTION getReadpermissionsRecursive(board INT) RETURNS text
DETERMINISTIC
  BEGIN
    DECLARE res TEXT DEFAULT '';
    CALL getReadpermissionsRecursive(board, res);
    RETURN res;
  END//
-- Function hasAccess checks recursively if a usergroups csv has access to a board
DROP FUNCTION IF EXISTS `hasAccess` //
CREATE FUNCTION `hasAccess`(board INT, usergroups TEXT) RETURNS BOOLEAN
DETERMINISTIC
  BEGIN
    DECLARE readPermissions TEXT DEFAULT NULL;
    SET readPermissions = getReadpermissionsRecursive(board);
    SET usergroups = REPLACE(usergroups, ',', '|');
    RETURN findMultipleInAndSet(usergroups, readPermissions);
  END //
DELIMITER ;