plugin.tx_thebridge {
	plugins {
		blog_list {
			label = FLOW3 Blog
			pid = 14
			# set type to 1 if you use old style frames
			type = 0
			patterns {
				10 = posts\.html\?tag\=
				20 = posts/.*?\.html$
				30 = posts\.html$
				40 = comments\/show
				50 = comments\/create
			}
		}
	}
}
