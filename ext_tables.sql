#
# Table structure for table 'tx_typo3bb_domain_model_forumcategory'
#
CREATE TABLE tx_typo3bb_domain_model_forumcategory (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    title varchar(255) DEFAULT '' NOT NULL,
    boards int(11) unsigned DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    sorting int(11) DEFAULT '0' NOT NULL,

    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
    l10n_diffsource mediumblob,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_typo3bb_domain_model_board'
#
CREATE TABLE tx_typo3bb_domain_model_board (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    title varchar(255) DEFAULT '' NOT NULL,
    description text NOT NULL,
    redirect varchar(255) DEFAULT '' NOT NULL,
    redirect_count int(11) unsigned DEFAULT '0' NOT NULL,
    topics int(11) unsigned DEFAULT '0' NOT NULL,
    sub_boards int(11) unsigned DEFAULT '0' NOT NULL,
    read_permissions varchar(255) DEFAULT '' NOT NULL,
    write_permissions varchar(255) DEFAULT '' NOT NULL,
    moderators varchar(255) DEFAULT '' NOT NULL,
    parent_board int(11) unsigned DEFAULT '0',
    forum_category int(11) unsigned DEFAULT '0',
    topics_count int(11) unsigned DEFAULT '0',
    posts_count int(11) unsigned DEFAULT '0',
    latest_post int(11) unsigned DEFAULT '0',
    latest_post_crdate int(11) unsigned DEFAULT NULL,
    subscribers int(11) unsigned DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    sorting int(11) DEFAULT '0' NOT NULL,

    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
    l10n_diffsource mediumblob,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_typo3bb_domain_model_topic'
#
CREATE TABLE tx_typo3bb_domain_model_topic (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    title varchar(255) DEFAULT '' NOT NULL,
    sticky tinyint(1) unsigned DEFAULT '0' NOT NULL,
    closed tinyint(1) unsigned DEFAULT '0' NOT NULL,
    posts_count int(11) DEFAULT '0' NOT NULL,
    latest_post_crdate int(11) unsigned DEFAULT '0' NOT NULL,
    posts int(11) unsigned DEFAULT '0' NOT NULL,
    poll int(11) unsigned DEFAULT '0',
    author int(11) unsigned DEFAULT '0',
    author_name varchar(255) DEFAULT '',
    subscribers int(11) unsigned DEFAULT '0' NOT NULL,
    readers int(11) unsigned DEFAULT '0' NOT NULL,
    latest_post int(11) unsigned DEFAULT '0',
    board int(11) unsigned DEFAULT '0' NOT NULL,
    views int(11) unsigned DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
    starttime int(11) unsigned DEFAULT '0' NOT NULL,
    endtime int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)

);

#
# Table structure for table 'tx_typo3bb_domain_model_post'
#
CREATE TABLE tx_typo3bb_domain_model_post (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    topic int(11) unsigned DEFAULT '0' NOT NULL,

    text text NOT NULL,
    author_name varchar(255) DEFAULT '' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    attachments int(11) unsigned NOT NULL default '0',
    author int(11) unsigned DEFAULT '0',
    editor int(11) unsigned DEFAULT '0',
    editor_name varchar(255) DEFAULT '' NOT NULL,
    edited tinyint(1) unsigned DEFAULT '0',

    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)

);

#
# Table structure for table 'tx_typo3bb_domain_model_poll'
#
CREATE TABLE tx_typo3bb_domain_model_poll (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    question varchar(255) DEFAULT '' NOT NULL,
    max_choices_select int(11) DEFAULT '0' NOT NULL,
    change_vote_allowed tinyint(1) unsigned DEFAULT '0' NOT NULL,
    vote_count int(11) DEFAULT '0' NOT NULL,
    result_hidden tinyint(1) unsigned DEFAULT '0' NOT NULL,
    choices int(11) unsigned DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,

    endtime int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)

);

#
# Table structure for table 'tx_typo3bb_domain_model_pollchoice'
#
CREATE TABLE tx_typo3bb_domain_model_pollchoice (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    text varchar(255) DEFAULT '' NOT NULL,
    vote_count int(11) DEFAULT '0' NOT NULL,
    poll int(11) unsigned DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)

);

#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (

    tx_typo3bb_display_name varchar(255) DEFAULT '' NOT NULL,
    signature text NOT NULL,
    created_topics int(11) unsigned DEFAULT '0' NOT NULL,
    subscribed_topics int(11) unsigned DEFAULT '0' NOT NULL,
    subscribed_boards int(11) unsigned DEFAULT '0' NOT NULL,
    created_posts int(11) unsigned DEFAULT '0' NOT NULL,
    edited_posts int(11) unsigned DEFAULT '0' NOT NULL,
    posts_count int(11) unsigned DEFAULT '0' NOT NULL,
    selected_poll_choices varchar(255) DEFAULT '' NOT NULL,
    voted_polls varchar(255) DEFAULT '' NOT NULL,
    sent_messages int(11) unsigned DEFAULT '0' NOT NULL,
    received_messages int(11) unsigned DEFAULT '0' NOT NULL,
    hide_sensitive_data tinyint(1) unsigned DEFAULT '0' NOT NULL,
    show_online tinyint(1) unsigned DEFAULT '1' NOT NULL,
    message_notification tinyint(1) unsigned DEFAULT '1' NOT NULL,
    login_time int(11) unsigned DEFAULT '0' NOT NULL,
    last_read_post int(11) unsigned DEFAULT '0' NOT NULL,
    read_topics int(11) unsigned DEFAULT '0' NOT NULL,

    tx_extbase_type varchar(255) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_typo3bb_domain_model_reader (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    user int(11) unsigned DEFAULT '0' NOT NULL,
    topic int(11) unsigned DEFAULT '0' NOT NULL,
    post int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY uid_local (user),
    KEY uid_foreign (topic)
);

#
# Table structure for table 'tx_typo3bb_topic_subscribers_mm'
#
CREATE TABLE tx_typo3bb_topic_subscribers_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_typo3bb_board_subscribers_mm'
#
CREATE TABLE tx_typo3bb_board_subscribers_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_typo3bb_frontenduser_pollchoice_mm'
#
CREATE TABLE tx_typo3bb_frontenduser_pollchoice_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_typo3bb_domain_model_message'
#
CREATE TABLE tx_typo3bb_domain_model_message (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    subject varchar(255) DEFAULT '' NOT NULL,
    text text NOT NULL,
    sender int(11) unsigned DEFAULT '0' NOT NULL,
    receivers int(11) unsigned DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_typo3bb_domain_model_messageparticipant
#
CREATE TABLE tx_typo3bb_domain_model_messageparticipant (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    sent_message int(11) unsigned DEFAULT '0' NOT NULL,
    received_message int(11) unsigned DEFAULT '0' NOT NULL,
    viewed tinyint(1) unsigned DEFAULT '0' NOT NULL,
    user int(11) unsigned DEFAULT '0' NOT NULL,
    user_name varchar(255) DEFAULT '' NOT NULL,
    deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_typo3bb_domain_model_attachment
#
CREATE TABLE tx_typo3bb_domain_model_attachment (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    post int(11) unsigned NOT NULL DEFAULT '0',
    file int(11) unsigned NOT NULL DEFAULT '0',
    original_file_name varchar(255) DEFAULT '' NOT NULL,
    download_count int(11) unsigned NOT NULL DEFAULT '0',

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_typo3bb_domain_model_statistic (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    date date DEFAULT '1000-01-01' NOT NULL,
    topics int(11) DEFAULT '0' NOT NULL,
    posts int(11) DEFAULT '0' NOT NULL,
    registers int(11) DEFAULT '0' NOT NULL,
    most_on int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    UNIQUE KEY unique_date (date)
);