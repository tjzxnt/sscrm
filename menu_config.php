<?php
return array(
	/*
	array(
		"title" => "基本设置",
		"icon" => "&#xe615;",
		"menu" => array(
			array("competence" => "NONE", "m_identity" => "operate", "submenu"=>"基本设置", "url" => spUrl("main", "config")),
			array("competence" => "", "depart_id" => array(), "submenu"=>"修改个人资料", "url" => spUrl("basic", "mypwd")),
			//array("competence" => "", "depart_id" => array(), "submenu"=>"IP安全设置", "url" => spUrl("basic", "ipsafesetting")),
		)
	),
	*/
	array(
		"title" => "日程管理",
		"icon" => "&#xe64e;",
		"toindex" => "rcgl",
		"menu" => array(
			array("competence" => "", "depart_id" => array(), "submenu"=>"我的动态", "url" => spUrl("schedule", "myplanlist"), "tail"=>'#schedule_myplanlist#'),
			array("competence" => "", "depart_id" => array(), "submenu"=>"所有人的动态", "url" => spUrl("schedule", "planlist"), "tail"=>'#schedule_planlist#'),
		)
	),
	array(
		"title" => "员工设置",
		"icon" => "&#xe612;",
		"menu" => array(
			array("competence" => "USER", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"员工管理", "url" => spUrl("users", "userslist")),
			array("competence" => "USER", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"员工回收站", "url" => spUrl("users", "recycle")),
		)
	),
	array(
		"title" => "蓄水客户",
		"icon" => "&#xe644;",
		"toindex" => "xskh",
		"menu" => array(
			/*
			array("competence" => "", "u_identity"=>"telclient", "depart_id" => array(2), "submenu"=>"我的CALL客客户", "url" => spUrl("clientintention", "clientlist", array("type"=>"1"))),
			array("competence" => "", "depart_id" => array(2), "submenu"=>"渠道客户", "url" => spUrl("clientintention", "clientlist", array("type"=>"2"))),
			array("competence" => "", "depart_id" => array(3), "u_identity"=>"getclient", "submenu"=>"线上客户", "url" => spUrl("clientintention", "clientlist", array("type"=>"3"))),
			array("competence" => "", "depart_id" => array(3), "u_identity"=>"getclient", "submenu"=>"同事介绍客户", "url" => spUrl("clientintention", "clientlist", array("type"=>"4"))),
			array("competence" => "", "depart_id" => array(3), "u_identity"=>"getclient", "submenu"=>"正常接电客户", "url" => spUrl("clientintention", "clientlist", array("type"=>"5"))),
			*/
			array("competence" => "", "depart_id" => array(2,3), "submenu"=>"我的蓄水客户", "url" => spUrl("clientintention", "clientlist"), "tail"=>'#clientintention_clientlist#'),
			array("competence" => "", "u_identity"=>"telclient_viewall", "depart_id" => array(), "submenu"=>"全部CALL客客户", "url" => spUrl("clientintention", "allclientlist", array("type"=>"1")), "tail"=>'#clientintention_allclientlist_1#'),
			array("competence" => "", "u_identity"=>"telclient_viewall", "depart_id" => array(), "submenu"=>"CALL客跟踪记录", "url" => spUrl("clientintention", "allrecordlist", array("type"=>"1")), "tail"=>'#clientintention_allrecordlist_1#'),
			array("competence" => "", "depart_id" => array(2,3), "isdirector"=>"1", "submenu"=>"部门蓄水客户", "url" => spUrl("clientintention", "allclientlist"), "tail"=>'#clientintention_allclientlist#'),
			array("competence" => "", "depart_id" => array(2,3), "isdirector"=>"1", "submenu"=>"部门回访记录", "url" => spUrl("clientintention", "allrecordlist"), "tail"=>'#clientintention_allrecordlist#'),
			array("competence" => "", "depart_id" => array(2,3), "isdirector"=>"1", "submenu"=>"部门计划列表", "url" => spUrl("clientintention", "allplanlist"), "tail"=>'#clientintention_allplanlist#'),
			array("competence" => "", "depart_id" => array(1), "submenu"=>"全部蓄水客户", "url" => spUrl("clientintention_m", "allclientlist"), "tail"=>'#clientintention_m_allclientlist#'),
			array("competence" => "", "depart_id" => array(1), "submenu"=>"全部回访记录", "url" => spUrl("clientintention_m", "allrecordlist"), "tail"=>'#clientintention_m_allrecordlist#'),
			array("competence" => "", "depart_id" => array(1), "submenu"=>"全部计划列表", "url" => spUrl("clientintention_m", "allplanlist"), "tail"=>'#clientintention_m_allplanlist#'),
		)
	),
	/*
	array(
		"title" => "蓄水客户统计",
		"icon" => "&#xe644;",
		"menu" => array(
			array("competence" => "", "u_identity"=>"telclient_viewall", "mark"=>"2_director", "depart_id" => array(), "submenu"=>"全部CALL客客户", "url" => spUrl("clientintention", "allclientlist", array("type"=>"1"))),
			array("competence" => "", "u_identity"=>"telclient_viewall", "mark"=>"2_director", "depart_id" => array(), "submenu"=>"CALL客回访记录", "url" => spUrl("clientintention", "allrecordlist", array("type"=>"1"))),
			array("competence" => "NONE", "mark"=>"2_director", "submenu"=>"全部渠道客户", "url" => spUrl("clientintention", "allclientlist", array("type"=>"2"))),
			array("competence" => "NONE", "mark"=>"2_director", "submenu"=>"渠道回访记录", "url" => spUrl("clientintention", "allrecordlist", array("type"=>"2"))),
			array("competence" => "NONE", "mark"=>"3_director", "submenu"=>"全部线上客户", "url" => spUrl("clientintention", "allclientlist", array("type"=>"3"))),
			array("competence" => "NONE", "mark"=>"3_director", "submenu"=>"线上回访记录", "url" => spUrl("clientintention", "allrecordlist", array("type"=>"3"))),
			array("competence" => "NONE", "mark"=>"3_director", "submenu"=>"同事介绍客户", "url" => spUrl("clientintention", "allclientlist", array("type"=>"4"))),
			array("competence" => "NONE", "mark"=>"3_director", "submenu"=>"介绍回访记录", "url" => spUrl("clientintention", "allrecordlist", array("type"=>"4"))),
			array("competence" => "NONE", "mark"=>"3_director", "submenu"=>"正常接电客户", "url" => spUrl("clientintention", "allclientlist", array("type"=>"5"))),
			array("competence" => "NONE", "mark"=>"3_director", "submenu"=>"接电回访记录", "url" => spUrl("clientintention", "allrecordlist", array("type"=>"5"))),
		)
	),
	*/
	array(
		"title" => "到访客户",
		"icon" => "&#xe612;",
		"toindex" => "dfkh",
		"menu" => array(
			//array("competence" => "CLIENTVISIT", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"客户来访管理", "url" => spUrl("clientvisits", "clientlist")),
			array("competence" => "", "depart_id" => array(), "isceo"=>0, "u_identity"=>"", "submenu"=>"与我相关的客户", "url" => spUrl("clientsrelated", "clientlist"), "tail"=>'#clientsrelated_clientlist#'),
			array("competence" => "CLIENTALL", "m_identity"=>"operatex", "depart_id" => array(), "submenu"=>"系统客户管理", "url" => spUrl("clientall", "clientlist"), "tail"=>'#clientall_clientlist#'),
			array("competence" => "CLIENTALL", "m_identity"=>"operatex", "depart_id" => array(), "submenu"=>"全部跟踪记录", "url" => spUrl("clientall", "allrecordlist"), "tail"=>'#clientall_allrecordlist#'),
			array("competence" => "CLIENTALL", "m_identity"=>"operatex", "depart_id" => array(), "submenu"=>"全部计划任务", "url" => spUrl("clientall", "planlist"), "tail"=>'#clientall_planlist#'),
			array("competence" => "CLIENTALL", "m_identity"=>"operatex", "depart_id" => array(), "submenu"=>"全部过期记录", "url" => spUrl("clientall", "allodlist"), "tail"=>'#clientall_allodlist#'),
			array("competence" => "", "depart_id" => array(6), "isdirector"=>1, "isceo"=>0, "submenu"=>"系统客户管理", "url" => spUrl("clientadm", "clientlist"), "tail"=>'#clientadm_clientlist#'),
			array("competence" => "CLIENTDEAL", "m_identity"=>"operatex,settlex", "depart_id" => array(), "submenu"=>"成交客户管理", "url" => spUrl("clientdeal", "clientlist"), "tail"=>'#clientdeal_clientlist#'),
			//array("competence" => "CLIENT", "u_identity"=>"getclient", "submenu"=>"我添加的客户", "url" => spUrl("clients", "mycreateclientlist")),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "submenu"=>"我的成交客户", "url" => spUrl("clientsales", "mypayclientlist"), "tail"=>'#clientsales_mypayclientlist#'),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "submenu"=>"我的跟进客户", "url" => spUrl("clientsales", "myclientlist"), "tail"=>'#clientsales_myclientlist#'),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "submenu"=>"我的客户计划", "url" => spUrl("clientsales", "planlist"), "tail"=>'#clientsales_planlist#'),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "submenu"=>"我的过期记录", "url" => spUrl("clientsales", "allodlist"), "tail"=>'#clientsales_allodlist#'),
			//array("competence" => "CLIENTSALE", "depart_id" => array(3), "u_identity"=>"getclient", "submenu"=>"公共客户池", "url" => spUrl("clientspool", "poolclientlist")),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "isdirector"=>1, "mark"=>"clientover", "submenu"=>"{{sep_name:销售部}}无意向客户", "url" => spUrl("clientsover", "clientlist"), "tail"=>'#clientsover_clientlist#'),
			array("competence" => "CLIENTOVERSEAS", "submenu"=>"我的海外客户", "isdirector"=>1, "url" => spUrl("clientoverseas", "myclientlist"), "tail"=>'#clientoverseas_myclientlist#'),
			array("competence" => "CLIENTTRANSFER", "submenu"=>"我可分配的客户", "url" => spUrl("clienttransfers", "clientlist")),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "isdirector"=>1, "submenu"=>"{{sep_name:销售部}}成交客户", "mark"=>"viewallclient", "url" => spUrl("clientdepart", "payclientlist"), "tail"=>'#clientdepart_payclientlist#'),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "isdirector"=>1, "submenu"=>"{{sep_name:销售部}}跟进客户", "mark"=>"viewallclient", "url" => spUrl("clientdepart", "clientlist"), "tail"=>'#clientdepart_clientlist#'),
			array("competence" => "CLIENTSALE", "depart_id" => array(3), "isdirector"=>1, "submenu"=>"{{sep_name:销售部}}客户计划", "url" => spUrl("clientdepart", "planlist"), "tail"=>'#clientdepart_planlist#'),
			array("competence" => "CLIENTSALE", "u_identity"=>"getclient", "isdirector"=>1, "submenu"=>"{{sep_name:销售部}}过期记录", "url" => spUrl("clientdepart", "allodlist"), "tail"=>'#clientdepart_allodlist#'),
			array("competence" => "CLIENTOVERSEAS", "submenu"=>"销售部客户", "isdirector"=>1, "url" => spUrl("clientdepart_oversea", "clientlist"), "tail"=>'#clientdepart_oversea_clientlist#'),
			array("competence" => "CLIENTSALE", "submenu"=>"全部跟踪记录", "isdirector"=>1, "url" => spUrl("clientdepart", "allrecordlist"), "tail"=>'#clientdepart_allrecordlist#'),
			array("competence" => "CLIENTOVERSEAS", "submenu"=>"全部跟踪记录", "isdirector"=>1, "url" => spUrl("clientdepart_oversea", "allrecordlist"), "tail"=>'#clientdepart_oversea_allrecordlist#'),
			array("competence" => "CHANNEL", "depart_id" => array(), "submenu"=>"我的渠道客户", "url" => spUrl("channels", "clientlist"), "tail"=>'#channels_clientlist#'),
			array("competence" => "CHANNEL", "depart_id" => array(), "isdirector"=>1, "submenu"=>"市场渠道客户", "url" => spUrl("channeldeparts", "clientlist"), "tail"=>'#channeldeparts_clientlist#'),
		)
	),
	array(
		"title" => "大客户管理",
		"icon" => "&#xe612;",
		"toindex" => "dkhgl",
		"menu" => array(
			array("competence" => "", "m_identity"=>"", "isceo"=>0, "depart_id" => array(), "submenu"=>"我的大客户", "url" => spUrl("vipclients", "clientlist"), "tail"=>'#vipclients_clientlist#'),
			array("competence" => "", "m_identity"=>"", "isceo"=>0, "depart_id" => array(), "submenu"=>"我的跟踪记录", "url" => spUrl("vipclients", "allrecordlist"), "tail"=>'#vipclients_allrecordlist#'),
			array("competence" => "CLIENTOVERSEAS", "isdirector"=>1, "depart_id" => array(), "submenu"=>"我的海外客户", "url" => spUrl("vipclientoverseas", "myclientlist"), "tail"=>'#vipclientoverseas_myclientlist#'),
			array("competence" => "", "isdirector"=>1, "isceo"=>0, "m_identity"=>"", "depart_id" => array(), "submenu"=>"部门大客户管理", "url" => spUrl("vipclientdepart", "clientlist"), "tail"=>'#vipclientdepart_clientlist#'),
			array("competence" => "", "isdirector"=>1, "isceo"=>0, "m_identity"=>"", "depart_id" => array(), "submenu"=>"部门跟踪记录", "url" => spUrl("vipclientdepart", "allrecordlist"), "tail"=>'#vipclientdepart_allrecordlist#'),
			array("competence" => "", "isdirector"=>1, "isceo"=>0, "m_identity"=>"", "depart_id" => array(), "submenu"=>"部门过期记录", "url" => spUrl("vipclientdepart", "allodlist"), "tail"=>'#vipclientdepart_allodlist#'),
			array("competence" => "VIPCLIENTALL", "m_identity"=>"", "depart_id" => array(), "submenu"=>"大客户管理", "url" => spUrl("vipclientall", "clientlist"), "tail"=>'#vipclientall_clientlist#'),
			array("competence" => "VIPCLIENTALL", "m_identity"=>"", "depart_id" => array(), "submenu"=>"全部跟踪记录", "url" => spUrl("vipclientall", "allrecordlist"), "tail"=>'#vipclientall_allrecordlist#'),
			array("competence" => "VIPCLIENTALL", "m_identity"=>"", "depart_id" => array(), "submenu"=>"全部过期记录", "url" => spUrl("vipclientall", "allodlist"), "tail"=>'#vipclientall_allodlist#'),
			array("competence" => "", "m_identity"=>"", "depart_id" => array(6), "isdirector"=>1, "submenu"=>"大客户管理", "url" => spUrl("vipclientadm", "clientlist"), "tail"=>'#vipclientadm_clientlist#'),
		)
	),
	array(
		"title" => "房源国家",
		"icon" => "&#xe614;",
		"menu" => array(
			array("competence" => "COUNTRY", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"房源国家", "url" => spUrl("countries", "countrylist")),
		)
	),
	array(
		"title" => "渠道管理",
		"icon" => "&#xe652;	",
		"toindex" => "qdgl",
		"menu" => array(
			//array("competence" => "MCHANNEL", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"渠道管理", "url" => spUrl("mchannels", "channellist")),
			array("competence" => "", "depart_id" => array(), "isceo"=>0, "submenu"=>"我的推荐渠道", "url" => spUrl("channels", "channelsignlist"), "tail"=>'#channels_channelsignlist#'),
			array("competence" => "CHANNEL", "depart_id" => array(), "isceo"=>0, "submenu"=>"我的维护渠道", "url" => spUrl("channels", "channellist"), "tail"=>'#channels_channellist#'),
			array("competence" => "CHANNEL", "submenu"=>"我的渠道计划", "url" => spUrl("channels", "planlist"), "tail"=>'#channels_planlist#'),
			array("competence" => "CHANNEL", "submenu"=>"我的过期记录", "url" => spUrl("channels", "allodlist"), "tail"=>'#channels_allodlist#'),
			array("competence" => "CHANNEL", "depart_id" => array(2), "isdirector"=>1, "submenu"=>"市场部渠道", "url" => spUrl("channeldeparts", "channellist"), "tail"=>'#channeldeparts_channellist#'),
			array("competence" => "CHANNEL", "depart_id" => array(2), "isdirector"=>1, "submenu"=>"市场部无意向渠道", "url" => spUrl("channelsover", "channellist"), "tail"=>'#channelsover_channellist#'),
			array("competence" => "CHANNEL", "depart_id" => array(2), "isdirector"=>1, "submenu"=>"市场部渠道计划", "url" => spUrl("channeldeparts", "planlist"), "tail"=>'#channeldeparts_planlist#'),
			array("competence" => "CHANNEL", "depart_id" => array(2), "isdirector"=>1, "submenu"=>"市场部过期记录", "url" => spUrl("channeldeparts", "allodlist"), "tail"=>'#channeldeparts_allodlist#'),
			array("competence" => "CHANNEL", "depart_id" => array(2), "isdirector"=>1, "submenu"=>"渠道待审核", "url" => spUrl("channeldeparts", "verifychannellist"), "tail"=>'#channeldeparts_verifychannellist#'),
			array("competence" => "CHANNEL", "depart_id" => array(2), "isdirector"=>1, "submenu"=>"全部跟踪记录", "url" => spUrl("channeldeparts", "allrecordlist"), "tail"=>'#channeldeparts_allrecordlist#'),
			array("competence" => "CHANNELVERIRY", "isdirector"=>1, "submenu"=>"渠道签约管理", "url" => spUrl("channelverifys", "verifychannellist"), "tail"=>'#channelverifys_verifychannellist#'),
			array("competence" => "CHANNELVERIRY", "isdirector"=>1, "m_identity"=>"operatex", "submenu"=>"全部签约记录", "url" => spUrl("channelverifys", "allsignlist"), "tail"=>'#channelverifys_allsignlist#'),
			array("competence" => "CHANNELALL", "depart_id" => array(), "submenu"=>"系统渠道管理", "url" => spUrl("channelall", "channellist"), "tail"=>'#channelall_channellist#'),
			array("competence" => "CHANNELALL", "depart_id" => array(), "submenu"=>"全部跟踪记录", "url" => spUrl("channelall", "allrecordlist"), "tail"=>'#channelall_allrecordlist#'),
			array("competence" => "CHANNELALL", "depart_id" => array(), "submenu"=>"全部签约记录", "url" => spUrl("channelall", "allsignlist"), "tail"=>'#channelall_allsignlist#'),
			array("competence" => "CHANNELALL", "depart_id" => array(), "submenu"=>"全部活动记录", "url" => spUrl("channelall", "allactlist"), "tail"=>'#channelall_allactlist#'),
			array("competence" => "CHANNELALL", "depart_id" => array(), "submenu"=>"全部计划任务", "url" => spUrl("channelall", "planlist"), "tail"=>'#channelall_planlist#'),
			array("competence" => "CHANNELALL", "depart_id" => array(), "submenu"=>"全部过期记录", "url" => spUrl("channelall", "allodlist"), "tail"=>'#channelall_allodlist#'),
		)
	),
	/*
	array(
		"title" => "分销商管理",
		"icon" => "&#xe60e;",
		"menu" => array(
			array("competence" => "MTRADER", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"分销商管理", "url" => spUrl("mtraders", "traderlist")),
			array("competence" => "TRADER", "depart_id" => array(), "submenu"=>"我的分销商", "url" => spUrl("traders", "traderlist")),
			array("competence" => "TRADER", "depart_id" => array(), "isdirector"=>1, "submenu"=>"我的部门分销商", "url" => spUrl("traderdeparts", "traderlist")),
		)
	),
	*/
	array(
		"title" => "旅行社管理",
		"icon" => "&#xe60e;",
		"menu" => array(
			array("competence" => "MTRAVEL", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"旅行社管理", "url" => spUrl("mtravels", "travellist"))
		)
	),
	/*
	array(
		"title" => "财务管理", //财务
		"icon" => "&#xe613;",
		"menu" => array(
			array("competence" => "FINANCE", "submenu"=>"销售完成的客户", "url" => spUrl("clientfinance", "saleclientlist")),
			array("competence" => "FINANCE", "submenu"=>"海外完成的客户", "url" => spUrl("clientfinance", "overseaclientlist")),
		)
	),
	array(
		"title" => "ERP修改单",
		"icon" => "&#xe61b;",
		"menu" => array(
			array("competence" => "MODIFYFORM_CREATE", "depart_id" => array(), "submenu"=>"创建修改单", "url" => spUrl("modifyforms", "cformlist")),
			array("competence" => "MODIFYFORM_MANAGE", "m_identity"=>"operate", "depart_id" => array(), "submenu"=>"处理修改单", "url" => spUrl("modifyforms", "dformlist")),
		)
	),
	
	array(
		"title" => "销售任务",
		"icon" => "&#xe616;",
		"menu" => array(
			array("competence" => "", "depart_id_adv" => array("2"=>"","3"=>"getclient"), "submenu"=>"我的部门任务", "mark"=>"", "url" => spUrl("pertasks", "departasklist")),
			array("competence" => "PERTASK", "depart_id" => array(2), "submenu"=>"市场部任务管理", "mark"=>"markettask", "url" => spUrl("pertasks", "markettasklist")),
			array("competence" => "PERTASK", "depart_id" => array(3), "submenu"=>"{{sep_name:销售部}}任务管理", "mark"=>"saletask", "url" => spUrl("pertasks", "saletasklist")),
		)
	),
	array(
		"title" => "日常数据统计",
		"icon" => "&#xe616;",
		"menu" => array(
			array("competence" => "PERFORMANCE", "isdirector"=>1, "m_identity"=>"operatex", "submenu"=>"销售待客统计", "url" => spUrl("statistics", "sales")),
			array("competence" => "PERFORMANCE", "isdirector"=>1, "m_identity"=>"operatex", "submenu"=>"市场接待统计", "url" => spUrl("statistics", "markets")),
			array("competence" => "PERFORMANCE", "isdirector"=>1, "m_identity"=>"operatex", "submenu"=>"其他接待统计", "url" => spUrl("statistics", "others")),
		)
	),
	array(
		"title" => "业绩统计",
		"icon" => "&#xe616;",
		"menu" => array(
			array("competence" => "", "u_identity"=>"getclient", "depart_id" => array(2,3,4,5,6), "submenu"=>"我的业绩统计", "url" => spUrl("statperfer", "mystatinfo")),
			array("competence" => "PERFORMANCE", "m_identity"=>"operatex,settle", "depart_id" => array(), "submenu"=>"市场部业绩统计", "mark"=>"marketstat", "url" => spUrl("statperfer", "marketstat")),
			array("competence" => "PERFORMANCE", "m_identity"=>"operatex,settle", "depart_id" => array(), "submenu"=>"{{sep_name:销售部}}业绩统计", "mark"=>"salestat", "url" => spUrl("statperfer", "salestat")),
			
			//array("competence" => "PERFORMANCE", "m_identity"=>"operate,settle", "depart_id" => array(), "submenu"=>"海外部业绩统计", "url" => spUrl("statperfer", "overseastat")),
			//array("competence" => "PERFORMANCE", "m_identity"=>"operate,settle", "depart_id" => array(), "submenu"=>"行政部业绩统计", "url" => spUrl("statperfer", "butlerstat")),
			//array("competence" => "PERFORMANCE", "m_identity"=>"operate,settle", "depart_id" => array(), "submenu"=>"技术部业绩统计", "url" => spUrl("statperfer", "techstat")),
			//array("competence" => "PERFORMANCE", "m_identity"=>"operate,settle", "depart_id" => array(), "submenu"=>"广宣部业绩统计", "url" => spUrl("statperfer", "advertstat")),
			
			array("competence" => "PERFORMANCE", "m_identity"=>"operatex,settle", "depart_id" => array(), "submenu"=>"渠道业绩统计", "url" => spUrl("statperfer", "channelstat")),
			//array("competence" => "PERFORMANCE", "m_identity"=>"operate,settle", "depart_id" => array(), "submenu"=>"分销商业绩统计", "url" => spUrl("statperfer", "traderstat")),
			array("competence" => "PERFORMANCE", "m_identity"=>"operatex,settle", "depart_id" => array(), "submenu"=>"旅行社业绩统计", "url" => spUrl("statperfer", "travelstat")),
		)
	),
	array(
		"title" => "通知管理",
		"icon" => "&#xe61b;",
		"menu" => array(
			array("competence" => "DEL", "depart_id" => array(), "submenu"=>"我的通知", "url" => spUrl("notices", "noticelist")),
		)
	),
	
	array(
		"title" => "操作日志管理",
		"icon" => "&#xe61a;",
		"menu" => array(
			array("competence" => "", "depart_id" => array(), "submenu"=>"我的操作日志", "url" => spUrl("logs", "loglist")),
		)
	),
	*/
);
?>