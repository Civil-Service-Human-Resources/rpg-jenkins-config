INSERT INTO wordpress.wp_users (user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name) 
VALUES ('ContentAuthor1','$P$BeqDIsbhy4QzTrvLLDcsBD6FILOaW7/','contentauthor1','contentauthor1@valtech.co.uk','',NOW(),'',0,'Content Author 1');

SELECT @id:=ID FROM wordpress.wp_users WHERE ID = last_insert_id();
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_capabilities', 'a:1:{s:14:"content_author";b:1;}');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_user_level', '0');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'nickname', 'contentauthor1');


INSERT INTO wordpress.wp_users (user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name) 
VALUES ('ContentAdmin1','$P$BkXpByBKxcrN1LZcuGOEOA5OTsWuLc.','contentadmin1','contentadmin1@valtech.co.uk','',NOW(),'',0,'Content Admin 1');

SELECT @id:=ID FROM wordpress.wp_users WHERE ID = last_insert_id();
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_capabilities', 'a:1:{s:13:"content_admin";b:1;}');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_user_level', '0');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'nickname', 'contentadmin1');


INSERT INTO wordpress.wp_users (user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name) 
VALUES ('ContentApprover1','$P$BQRLkO/D2Q3sAkzsRMMY2UkjWtmeDj/','contentapprover1','contentapprover1@valtech.co.uk','',NOW(),'',0,'Content Approver 1');

SELECT @id:=ID FROM wordpress.wp_users WHERE ID = last_insert_id();
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_capabilities', 'a:1:{s:16:"content_approver";b:1;}');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_user_level', '0');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'nickname', 'contentapprover1');


INSERT INTO wordpress.wp_users (user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name) 
VALUES ('ContentPublisher1','$P$Bf3NZ05ig9V.NOCqRCaoqIfdHmqo5J.','contentpublisher1','contentpublisher1@valtech.co.uk','',NOW(),'',0,'Content Publisher 1');

SELECT @id:=ID FROM wordpress.wp_users WHERE ID = last_insert_id();
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_capabilities', 'a:1:{s:17:"content_publisher";b:1;}');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_user_level', '0');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'nickname', 'contentpublisher1');


INSERT INTO wordpress.wp_users (user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name) 
VALUES ('ContentSnippets1','$P$BorGglgZG0uyFsQRfM4YOvAoBVycvK0','contentsnippets1','contentsnippets1@valtech.co.uk','',NOW(),'',0,'Content Snippets 1');

SELECT @id:=ID FROM wordpress.wp_users WHERE ID = last_insert_id();
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_capabilities', 'a:1:{s:16:"content_snippets";b:1;}');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'wp_user_level', '0');
INSERT INTO wordpress.wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, @id, 'nickname', 'contentsnippets1');