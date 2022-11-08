create table statistics
(
  id          int auto_increment
    primary key,
  ip_address  varchar(20)                        not null,
  user_agent  varchar(200)                       not null,
  view_date   datetime default CURRENT_TIMESTAMP not null,
  page_url    text                               not null,
  views_count int default '1'                    not null
);

create index ip_address__idx
  on statistics (ip_address);

create fulltext index page_url__idx
  on statistics (page_url);

create index user_agent__idx
  on statistics (user_agent);