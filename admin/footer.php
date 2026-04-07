		</div>

	</div>

	<script src="js/jquery-2.2.4.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/bootstrap5-legacy-bridge.js"></script>
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/select2.full.min.js"></script>
	<script src="js/jquery.inputmask.js"></script>
	<script src="js/jquery.inputmask.date.extensions.js"></script>
	<script src="js/jquery.inputmask.extensions.js"></script>
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/icheck.min.js"></script>
	<script src="js/fastclick.js"></script>
	<script src="js/jquery.sparkline.min.js"></script>
	<script src="js/jquery.slimscroll.min.js"></script>
	<script src="js/jquery.fancybox.pack.js"></script>
	<script src="js/app.min.js"></script>
	<script src="js/jscolor.js"></script>
	<script src="js/on-off-switch.js"></script>
    <script src="js/on-off-switch-onload.js"></script>
    <script src="js/clipboard.min.js"></script>
	<script src="js/demo.js"></script>
	<script src="js/summernote.js"></script>

	<script>
		// Fallback (no-jQuery) handler for confirm-delete.
		// Ensures clicking "Xóa" always navigates to the stored delete URL.
		(function () {
			// If jQuery is available, prefer the main jQuery implementation below.
			if (window.jQuery) {
				return;
			}
			var STORAGE_KEY = 'admin-delete-return-state';

			function safeSetReturnState(partial) {
				try {
					var state = partial || {};
					if (state.path == null) {
						state.path = window.location.pathname;
					}
					if (state.scrollY == null) {
						state.scrollY = window.scrollY || window.pageYOffset || 0;
					}
					sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
				} catch (e) {
					// ignore
				}
			}

			function tryRestoreReturnState() {
				try {
					var params = new URLSearchParams(window.location.search || '');
					var isDeleteReturn = params.get('success') === 'deleted' || params.get('error') === 'delete_failed';
					if (!isDeleteReturn) {
						return;
					}
					var raw = sessionStorage.getItem(STORAGE_KEY);
					if (!raw) {
						return;
					}
					sessionStorage.removeItem(STORAGE_KEY);
					var state = JSON.parse(raw) || {};
					if (state.path && state.path !== window.location.pathname) {
						return;
					}
					var y = Number(state.scrollY || 0);
					window.setTimeout(function () {
						window.scrollTo(0, y);
					}, 0);
				} catch (e) {
					// ignore
				}
			}

			function closestMatch(el, selector) {
				while (el && el.nodeType === 1) {
					if (el.matches && el.matches(selector)) {
						return el;
					}
					el = el.parentElement;
				}
				return null;
			}

			function setDeleteHref(href) {
				var modal = document.getElementById('confirm-delete');
				if (!modal) {
					return;
				}
				modal.dataset.deleteHref = href || '';
				var ok = modal.querySelector('.btn-ok');
				if (ok) {
					ok.setAttribute('href', href || '#');
					ok.setAttribute('data-href', href || '');
				}
			}

			document.addEventListener('click', function (e) {
				var trigger = closestMatch(e.target, '[data-target="#confirm-delete"], [data-bs-target="#confirm-delete"]');
				if (trigger) {
					var href = trigger.getAttribute('data-href') || trigger.dataset.href || '';
					setDeleteHref(href);
					return;
				}

				var okBtn = closestMatch(e.target, '#confirm-delete .btn-ok');
				if (okBtn) {
					var modal = document.getElementById('confirm-delete');
					var hrefOk = okBtn.getAttribute('href') || okBtn.getAttribute('data-href') || (modal ? (modal.dataset.deleteHref || '') : '');
					if (!hrefOk || hrefOk === '#') {
						e.preventDefault();
						alert('Không lấy được link xóa. Bạn hãy tải lại trang (Ctrl+F5) và thử lại.');
						return;
					}
					safeSetReturnState({
						path: window.location.pathname,
						scrollY: window.scrollY || window.pageYOffset || 0
					});
					e.preventDefault();
					window.location.href = hrefOk;
				}
			}, true);

			// Attempt restore when returning from delete.
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', tryRestoreReturnState);
			} else {
				tryRestoreReturnState();
			}
		})();

		function formatThousandsInput(value) {
			var digitsOnly = String(value || '').replace(/\D/g, '');
			if (!digitsOnly) {
				return '';
			}
			return digitsOnly.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		}

		$(document).ready(function() {
	        $('#editor1').summernote({
	        	height: 300
	        });
	        $('#editor2').summernote({
	        	height: 300
	        });
	        $('#editor3').summernote({
	        	height: 300
	        });
	        $('#editor4').summernote({
	        	height: 300
	        });
	        $('#editor5').summernote({
	        	height: 300
	        });

	        $('.currency-input').each(function() {
	        	$(this).val(formatThousandsInput($(this).val()));
	        });

	        $(document).on('input', '.currency-input', function() {
	        	$(this).val(formatThousandsInput($(this).val()));
	        });
	    });
		$(".top-cat").on('change',function(){
			var id=$(this).val();
			var dataString = 'id='+ id;
			$.ajax
			({
				type: "POST",
				url: "get-mid-category.php",
				data: dataString,
				cache: false,
				success: function(html)
				{
					$(".mid-cat").html(html);
				}
			});			
		});
		$(".mid-cat").on('change',function(){
			var id=$(this).val();
			var dataString = 'id='+ id;
			$.ajax
			({
				type: "POST",
				url: "get-end-category.php",
				data: dataString,
				cache: false,
				success: function(html)
				{
					$(".end-cat").html(html);
				}
			});			
		});
	</script>

	<script>
	  $(function () {
		function normalizeAdminFormRows() {
			var isDesktop = window.matchMedia && window.matchMedia('(min-width: 992px)').matches;
			var $groups = $('.content .form-horizontal .form-group');
			$groups.removeClass('form-group-top');

			if (!isDesktop) {
				return;
			}

			$groups.each(function() {
				var $group = $(this);
				var hasTallControl = $group.find('textarea, input[type="file"], .note-editor, .existing-photo').length > 0;
				if (hasTallControl) {
					$group.addClass('form-group-top');
				}
			});
		}

		normalizeAdminFormRows();
		$(window).on('resize', normalizeAdminFormRows);

		function getDataTableLanguageVi() {
			return {
				processing: "Đang xử lý...",
				search: "Lọc:",
				lengthMenu: "Hiển thị _MENU_ mục",
				info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
				infoEmpty: "Hiển thị 0 đến 0 của 0 mục",
				infoFiltered: "(lọc từ _MAX_ mục)",
				loadingRecords: "Đang tải...",
				zeroRecords: "Không tìm thấy dữ liệu phù hợp",
				emptyTable: "Không có dữ liệu",
				paginate: {
					first: "Đầu",
					previous: "Trước",
					next: "Sau",
					last: "Cuối"
				},
				aria: {
					sortAscending: ": sắp xếp tăng dần",
					sortDescending: ": sắp xếp giảm dần"
				}
			};
		}

		function getOrInitDataTableVi(selector, options) {
			if (!window.jQuery || !$.fn || !$.fn.DataTable) {
				return null;
			}
			var $table = $(selector).first();
			if (!$table.length) {
				return null;
			}
			if ($.fn.DataTable.isDataTable($table[0])) {
				return $table.DataTable();
			}
			var language = getDataTableLanguageVi();
			var emptyTableMessage = $table.attr('data-empty-table') || $table.attr('data-dt-empty') || '';
			if (emptyTableMessage) {
				language.emptyTable = emptyTableMessage;
			}
			var config = $.extend(true, { language: language }, options || {});
			return $table.DataTable(config);
		}

		function enhanceDataTableSearchControls() {
			if (!window.jQuery || !$.fn || !$.fn.DataTable) {
				return;
			}

			$('.dataTables_wrapper .dataTables_filter').each(function() {
				var $filter = $(this);
				if ($filter.attr('data-search-enhanced') === '1') {
					return;
				}

				var $label = $filter.find('label').first();
				var $input = $label.find('input[type="search"]').first();
				if (!$label.length || !$input.length) {
					return;
				}

				var labelText = $.trim($label.clone().children().remove().end().text());
				if (labelText && labelText.toLowerCase().indexOf('tìm kiếm') !== -1) {
					labelText = 'Lọc:';
				}
				if (!labelText) {
					labelText = 'Lọc:';
				}

				var $box = $('<span class="dt-search-box"></span>');
				$input.attr('placeholder', 'Nhập từ khóa');
				$input.appendTo($box);

				var $btn = $('<button type="button" class="btn-dt-search">Tìm kiếm</button>');
				$box.append($btn);

				$label.empty();
				$label.append($('<span class="dt-search-text"></span>').text(labelText));
				$label.append($box);

				var $wrapper = $filter.closest('.dataTables_wrapper');
				var $table = $wrapper.find('table.dataTable').first();
				var dt = null;
				if ($table.length && $.fn.DataTable.isDataTable($table[0])) {
					dt = $table.DataTable();
				}

				var runSearch = function() {
					var value = $input.val();
					if (dt) {
						dt.search(value).draw();
					} else {
						$input.trigger('keyup');
					}
				};

				$btn.on('click', function(e) {
					e.preventDefault();
					runSearch();
				});

				$input.on('keydown', function(e) {
					if (e.key === 'Enter') {
						e.preventDefault();
						runSearch();
					}
				});

				$filter.attr('data-search-enhanced', '1');
			});
		}

		// --- Return state (scroll + DataTable) ---
		var STORAGE_KEY = 'admin-return-state';
		var STORAGE_TTL_MS = 1000 * 60 * 60 * 6; // 6 hours
		function setReturnStateWithDataTable() {
			try {
				var state = {
					ts: Date.now(),
					path: window.location.pathname,
					scrollY: window.scrollY || window.pageYOffset || 0
				};
				var dt = getOrInitDataTableVi('#example1');
				if (dt) {
					state.dtPage = dt.page();
					state.dtLen = dt.page.len();
					state.dtSearch = dt.search();
					state.dtOrder = dt.order();
				}
				sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
			} catch (e) {
				// ignore
			}
		}

		function restoreReturnStateWithDataTable() {
			try {
				var raw = sessionStorage.getItem(STORAGE_KEY);
				if (!raw) {
					return;
				}
				var state = JSON.parse(raw) || {};
				var ts = Number(state.ts || 0);
				if (ts && (Date.now() - ts) > STORAGE_TTL_MS) {
					sessionStorage.removeItem(STORAGE_KEY);
					return;
				}
				if (state.path && state.path !== window.location.pathname) {
					return;
				}
				sessionStorage.removeItem(STORAGE_KEY);
				if (typeof state.dtPage !== 'undefined') {
					var dt = getOrInitDataTableVi('#example1');
					if (dt) {
						var needsDraw = false;
						if (typeof state.dtLen !== 'undefined' && state.dtLen) {
							dt.page.len(state.dtLen);
							needsDraw = true;
						}
						if (typeof state.dtSearch === 'string') {
							dt.search(state.dtSearch);
							needsDraw = true;
						}
						if (typeof state.dtOrder !== 'undefined' && state.dtOrder) {
							dt.order(state.dtOrder);
							needsDraw = true;
						}
						dt.page(state.dtPage);
						needsDraw = true;
						if (needsDraw) {
							dt.draw(false);
						}
					}
				}
				var y = Number(state.scrollY || 0);
				setTimeout(function () {
					window.scrollTo(0, y);
				}, 0);
			} catch (e) {
				// ignore
			}
		}

		restoreReturnStateWithDataTable();
		function resolveDeleteHref($trigger) {
			if(!$trigger || !$trigger.length) {
				return '';
			}
			// Prefer explicit data-href, fallback to href if it isn't '#'
			var dataHref = $trigger.attr('data-href') || $trigger.data('href') || '';
			if(dataHref) {
				return dataHref;
			}
			var href = $trigger.attr('href') || '';
			if(href && href !== '#') {
				return href;
			}
			return '';
		}

		function isModifiedClick(e) {
			if (!e) {
				return false;
			}
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
				return true;
			}
			// Middle click, right click, etc.
			if (typeof e.which !== 'undefined' && e.which !== 1) {
				return true;
			}
			return false;
		}

		function shouldPreserveReturnStateForLink($link, e) {
			if (!$link || !$link.length) {
				return false;
			}
			if (isModifiedClick(e)) {
				return false;
			}
			if (e && typeof e.isDefaultPrevented === 'function' && e.isDefaultPrevented()) {
				return false;
			}
			var target = String($link.attr('target') || '').toLowerCase();
			if (target === '_blank') {
				return false;
			}
			var href = $.trim(String($link.attr('href') || ''));
			if (!href || href === '#' || href.charAt(0) === '#') {
				return false;
			}
			if (/^(javascript:|mailto:|tel:)/i.test(href)) {
				return false;
			}
			var toggle = String($link.attr('data-toggle') || $link.attr('data-bs-toggle') || '').toLowerCase();
			if (toggle === 'modal' || toggle === 'dropdown' || toggle === 'collapse' || toggle === 'offcanvas') {
				return false;
			}
			if ($link.is('[data-target], [data-bs-target]')) {
				return false;
			}
			return true;
		}

		// Preserve table paging + scroll when clicking action buttons.
		$(document).on('click', '.content a.btn', function(e) {
			var $link = $(this);
			if (!shouldPreserveReturnStateForLink($link, e)) {
				return;
			}
			setReturnStateWithDataTable();
		});

		// Never allow placeholder links inside admin content to jump to the top.
		$(document).on('click', '.content a[href="#"]', function(e) {
			if (e && typeof e.preventDefault === 'function') {
				e.preventDefault();
			}
		});

		// When a trigger is clicked, store delete URL immediately.
		$(document).on('click', '[data-target="#confirm-delete"], [data-bs-target="#confirm-delete"]', function(e) {
			// Never allow href="#" to jump to the top.
			if (e && typeof e.preventDefault === 'function') {
				e.preventDefault();
			}
			var href = resolveDeleteHref($(this));
			$('#confirm-delete').data('delete-href', href || '');
			$('#confirm-delete .btn-ok').attr('href', href || '#').attr('data-href', href || '');
		});

		// When modal is shown, also try to pick URL from relatedTarget (Bootstrap provides it).
		$('#confirm-delete').on('show.bs.modal', function(e) {
			var $related = e.relatedTarget ? $(e.relatedTarget) : null;
			var href = resolveDeleteHref($related) || $(this).data('delete-href') || '';
			$(this).data('delete-href', href || '');
			$(this).find('.btn-ok').attr('href', href || '#').attr('data-href', href || '');
		});

		// Force navigate on confirm click (works even if href is set late)
		$(document).on('click', '#confirm-delete .btn-ok', function(e) {
			var href = $(this).attr('href') || $(this).data('href') || $('#confirm-delete').data('delete-href') || '';
			if(!href || href === '#') {
				e.preventDefault();
				alert('Không lấy được link xóa. Bạn hãy tải lại trang (Ctrl+F5) và thử lại.');
				return false;
			}
			setReturnStateWithDataTable();
			e.preventDefault();
			window.location.href = href;
			return false;
		});

	    //Initialize Select2 Elements
		if ($.fn.select2) {
	    	$(".select2").select2();
		}

	    //Datemask dd/mm/yyyy
		if ($.fn.inputmask) {
	    	$("#datemask").inputmask("dd-mm-yyyy", {"placeholder": "dd-mm-yyyy"});
	    	//Datemask2 mm/dd/yyyy
	    	$("#datemask2").inputmask("mm-dd-yyyy", {"placeholder": "mm-dd-yyyy"});
	    	//Money Euro
	    	$("[data-mask]").inputmask();
		}

	    //Date picker
		if ($.fn.datepicker) {
	    	$('#datepicker').datepicker({
	    	  autoclose: true,
	    	  format: 'dd-mm-yyyy',
	    	  todayBtn: 'linked',
	    	});

	    	$('#datepicker1').datepicker({
	    	  autoclose: true,
	    	  format: 'dd-mm-yyyy',
	    	  todayBtn: 'linked',
	    	});
		}

	    //iCheck for checkbox and radio inputs
		if ($.fn.iCheck) {
	    	$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
	    	  checkboxClass: 'icheckbox_minimal-blue',
	    	  radioClass: 'iradio_minimal-blue'
	    	});
	    	//Red color scheme for iCheck
	    	$('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
	    	  checkboxClass: 'icheckbox_minimal-red',
	    	  radioClass: 'iradio_minimal-red'
	    	});
	    	//Flat red color scheme for iCheck
	    	$('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
	    	  checkboxClass: 'icheckbox_flat-green',
	    	  radioClass: 'iradio_flat-green'
	    	});
		}



		if ($.fn.DataTable) {
			getOrInitDataTableVi('#example1');
			getOrInitDataTableVi('#example2', {
				"paging": true,
				"lengthChange": false,
				"searching": false,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"language": {
					"emptyTable": "Không có sản phẩm nào dưới ngưỡng cảnh báo."
				}
			});
			enhanceDataTableSearchControls();
		}
		
		$('#confirm-approve').on('show.bs.modal', function(e) {
	      $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
	    });
 
	  });

		function confirmDelete()
	    {
	        return confirm("Bạn có chắc chắn muốn xóa dữ liệu này không?");
	    }
	    function confirmActive()
	    {
	        return confirm("Bạn có chắc chắn muốn kích hoạt mục này không?");
	    }
	    function confirmInactive()
	    {
	        return confirm("Bạn có chắc chắn muốn ẩn mục này không?");
	    }

	</script>

	<script type="text/javascript">
		function showDiv(elem){
			if(elem.value == 0) {
		      	document.getElementById('photo_div').style.display = "none";
		      	document.getElementById('icon_div').style.display = "none";
		   	}
		   	if(elem.value == 1) {
		      	document.getElementById('photo_div').style.display = "block";
		      	document.getElementById('photo_div_existing').style.display = "block";
		      	document.getElementById('icon_div').style.display = "none";
		   	}
		   	if(elem.value == 2) {
		      	document.getElementById('photo_div').style.display = "none";
		      	document.getElementById('photo_div_existing').style.display = "none";
		      	document.getElementById('icon_div').style.display = "block";
		   	}
		}
		function showContentInputArea(elem){
		   if(elem.value == 'Full Width Page Layout') {
		      	document.getElementById('showPageContent').style.display = "block";
		   } else {
		   		document.getElementById('showPageContent').style.display = "none";
		   }
		}
	</script>

	<script type="text/javascript">

        $(document).ready(function () {

			$("#btnAddNew").click(function () {

		        var rowNumber = $("#ProductTable tbody tr").length;

		        var trNew = "";              

		        var addLink = "<div class=\"upload-btn" + rowNumber + "\"><input type=\"file\" name=\"photo[]\"  style=\"margin-bottom:5px;\"></div>";
		           
				var deleteRow = "<a href=\"javascript:void()\" class=\"delete-photo-row btn btn-danger btn-xs\">Xóa</a>";

		        trNew = trNew + "<tr> ";

		        trNew += "<td>" + addLink + "</td>";
		        trNew += "<td style=\"width:28px;\">" + deleteRow + "</td>";

		        trNew = trNew + " </tr>";

		        $("#ProductTable tbody").append(trNew);

		    });

		    $('#ProductTable').delegate('a.delete-photo-row', 'click', function () {
		        $(this).parent().parent().fadeOut('slow').remove();
		        return false;
		    });

        });



        var items = [];
        for( i=1; i<=24; i++ ) {
        	items[i] = document.getElementById("tabField"+i);
        }

		if(items[1]) {
			items[1].style.display = 'block';
			items[2].style.display = 'block';
			items[3].style.display = 'block';
			items[4].style.display = 'none';

			items[5].style.display = 'block';
			items[6].style.display = 'block';
			items[7].style.display = 'block';
			items[8].style.display = 'none';

			items[9].style.display = 'block';
			items[10].style.display = 'block';
			items[11].style.display = 'block';
			items[12].style.display = 'none';

			items[13].style.display = 'block';
			items[14].style.display = 'block';
			items[15].style.display = 'block';
			items[16].style.display = 'none';

			items[17].style.display = 'block';
			items[18].style.display = 'block';
			items[19].style.display = 'block';
			items[20].style.display = 'none';

			items[21].style.display = 'block';
			items[22].style.display = 'block';
			items[23].style.display = 'block';
			items[24].style.display = 'none';
		}

		function funcTab1(elem) {
			if(!items[1]) { return; }
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[1].style.display = 'block';
		       	items[2].style.display = 'block';
		       	items[3].style.display = 'block';
		       	items[4].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[1].style.display = 'none';
		       	items[2].style.display = 'none';
		       	items[3].style.display = 'none';
		       	items[4].style.display = 'block';
			}
		};

		function funcTab2(elem) {
			if(!items[1]) { return; }
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[5].style.display = 'block';
		       	items[6].style.display = 'block';
		       	items[7].style.display = 'block';
		       	items[8].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[5].style.display = 'none';
		       	items[6].style.display = 'none';
		       	items[7].style.display = 'none';
		       	items[8].style.display = 'block';
			}
		};

		function funcTab3(elem) {
			if(!items[1]) { return; }
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[9].style.display = 'block';
		       	items[10].style.display = 'block';
		       	items[11].style.display = 'block';
		       	items[12].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[9].style.display = 'none';
		       	items[10].style.display = 'none';
		       	items[11].style.display = 'none';
		       	items[12].style.display = 'block';
			}
		};

		function funcTab4(elem) {
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[13].style.display = 'block';
		       	items[14].style.display = 'block';
		       	items[15].style.display = 'block';
		       	items[16].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[13].style.display = 'none';
		       	items[14].style.display = 'none';
		       	items[15].style.display = 'none';
		       	items[16].style.display = 'block';
			}
		};

		function funcTab5(elem) {
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[17].style.display = 'block';
		       	items[18].style.display = 'block';
		       	items[19].style.display = 'block';
		       	items[20].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[17].style.display = 'none';
		       	items[18].style.display = 'none';
		       	items[19].style.display = 'none';
		       	items[20].style.display = 'block';
			}
		};

		function funcTab6(elem) {
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[21].style.display = 'block';
		       	items[22].style.display = 'block';
		       	items[23].style.display = 'block';
		       	items[24].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[21].style.display = 'none';
		       	items[22].style.display = 'none';
		       	items[23].style.display = 'none';
		       	items[24].style.display = 'block';
			}
		};



        
    </script>

</body>
</html>
