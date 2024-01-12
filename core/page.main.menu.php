<div id="sidebar" class="offcanvas offcanvas-start" tabindex="-1">
	<div class="offcanvas-header">
        <a><img style="height: 32px;" src="assets/img/logo.png" alt="Logo"></a>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<div id="sidebar-nav" class="list-group border-0 rounded-0 text-sm-start">
			<ul class="nav nav-pills flex-column" id="menu">
				<script id="leftPanelJS">
					var modPath = "?req=module&module=";
					function buildSubMenu(key, submenuData) {
						var $submenu = $("<div>");
						$.each(submenuData, function(key, val) {
						var $submenuItem;

						if (val.type === "item") {
							$submenuItem = $("<li>").addClass("w-100 mb-1").append($("<a>", { href: modPath+val.path })
								.addClass("nav-link ajaxAction border border-primary")
								.append($("<i>").addClass("fas fa-"+val.icon))
								.append($("<span>", {text: val.name}).addClass("ms-1"))
							);
						} else if (val.type === "submenu") {
							$submenuItem = $("<li>")
								.append($("<a>", { href: "#"+key })
									.append($("<i>").addClass("fas fa-"+val.icon))
									.append($("<span>", {text: val.name}).addClass("ms-1"))
								)
								.append(buildSubMenu(key, val.items));
						}
						$submenu.append($submenuItem);
						});

						return $("<ul>").attr("id", key).addClass("collapse nav ms-2").append($submenu);
					}
							
					SPA.whenReady(function () {
						menu = JSON.parse('<?php global $DB; echo $DB->getMenu(); ?>');
						var $menu = $("#menu");
						$.each(menu, function(key, val) {
							var $menuItem;
							if (val.type === "item") {
								$menuItem = $("<li>").addClass("nav-item mb-1").append($("<a>", { href: modPath+val.path })
									.addClass("nav-link ajaxAction border border-primary")
									.append($("<i>").addClass("fas fa-"+val.icon))
									.append($("<span>", {text: val.name}).addClass("ms-1"))
								);
							} else if (val.type === "submenu") {
								$menuItem = $("<li>")
								.append($("<a>", { href: "#" +key })
									.attr("data-bs-toggle", "collapse")
									.addClass("nav-link border border-primary mb-1")
									.append($("<i>").addClass("fas fa-"+val.icon))
									.append($("<span>", {text: val.name}).addClass("ms-1"))
								)
								.append(buildSubMenu(key ,val.items));
							}
							$menu.append($menuItem);
						});
						$("#leftPanelJS").remove();
					});
					
					SPA.whenReady( function (e) {
						sidebar = new bootstrap.Offcanvas('#sidebar');
					});
				</script>
			</ul>
		</div>
	</div>
</div>