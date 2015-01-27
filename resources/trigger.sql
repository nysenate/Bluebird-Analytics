
DROP TRIGGER IF EXISTS request_before_insert;

DELIMITER //

CREATE DEFINER=CURRENT_USER TRIGGER request_before_insert
BEFORE INSERT ON request FOR EACH ROW BEGIN
  /* Initialize */
  SET @t_loc_id = NULL;
  SET @t_url_id = NULL;
  /* Populate location_id based on IP */
  SELECT id INTO @t_loc_id FROM location WHERE NEW.trans_ip BETWEEN ipv4_start AND ipv4_end;
  SET NEW.location_id = IFNULL(@t_loc_id,1);
  /* Clean the URL and try matching it to the known list */
  SET @clean_url = preg_replace(
     '#(.+)/(?:[0-9]+)?$|([a-z0-9]+),.*$|(/_vti_).*|(/user)/[0-9]+#',
     '$1$2$3$4', NEW.path);

  SELECT id INTO @t_url_id
    FROM url
    WHERE
      (match_full = 0 AND path=@clean_url)
      OR (match_full = 1 AND path=@clean_url AND preg_rlike(search, NEW.query))
    ORDER BY match_full DESC, path
    LIMIT 1;
  SET NEW.url_id = IFNULL(@t_url_id,1);
END
//

DELIMITER ;

