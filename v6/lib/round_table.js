/*
// ˵���� Ϊ������Բ��Ч�� (Բ��css��Ҫ������css�ļ��ж����)
// ���ߣ� ���� (934834734@qq.com) @ 2013-02-01
// �÷���
  1. Ϊtable��class���� round_table
  2. tableҪָ��һ��id
  3. <input type="hidden" id="���ID:width" value="���Ŀ��" />
  4. <input type="hidden" id="���ID:class" value="����Բ����ʽ,�ײ�Բ����ʽ" />
  5. ��Ҫ dom_loaded ����֧��
*/

function byid(id) {
	return document.getElementById(id);
}

// ���һ���ַ����Ƿ���������
function in_array(str, arr) {
	if (!arr.length || arr.length == 0) {
		return false;
	}
	for (var i = 0; i < arr.length; i++) {
		if (str == arr[i]) {
			return true;
		}
	}
	return false;
}

if (!document.all) {
	HTMLElement.prototype.insertAdjacentHTML = function(where, html) {
		var e = this.ownerDocument.createRange();
		e.setStartBefore(this);
		e = e.createContextualFragment(html);
		switch (where) {
			case 'beforeBegin': this.parentNode.insertBefore(e, this);break;
			case 'afterBegin': this.insertBefore(e, this.firstChild); break;
			case 'beforeEnd': this.appendChild(e); break;
			case 'afterEnd':
				if(!this.nextSibling) this.parentNode.appendChild(e);
				else this.parentNode.insertBefore(e, this.nextSibling); break;
		}
	};
}

function wee_add_round_table_top(o, className, width) {
	var html = '<table class="'+className+'" width="'+width+'"><tr><td class="rt_top"><div class="rt_top_left"></div><div class="rt_top_right"></div></td></tr></table>';
	o.insertAdjacentHTML("beforeBegin", html);

	o.style.borderTop = 0;
	// ����һ�е�border-top��Ϊ0:
	var tr = o.getElementsByTagName("TR");
	if (tr.length > 0) {
		var tr1 = tr[0];
		var td = tr1.getElementsByTagName("TD");
		if (td.length > 0) {
			for (var j = 0; j < td.length; j++) {
				td[j].style.borderTop = 0;
				td[j].style.paddingTop = "2px";
				td[j].style.paddingBottom = "5px";
			}
		}
	}
}

function wee_add_round_table_bottom(o, className, width) {
	var html = '<table class="'+className+'" width="'+width+'"><tr><td class="rt_bottom"><div class="rt_bottom_left"></div><div class="rt_bottom_right"></div></td></tr></table>';
	o.insertAdjacentHTML("afterEnd", html);

	o.style.borderBottom = 0;
	var tr = o.getElementsByTagName("TR");
	// ������һ�е�border-bottom ��Ϊ0:
	if (tr.length > 1) {
		var trn = tr[tr.length - 1];
		var td = trn.getElementsByTagName("TD");
		if (td.length > 0) {
			for (var j = 0; j < td.length; j++) {
				td[j].style.borderBottom = 0;
			}
		}
	}
}

function wee_add_round_table() {
	if (window.round_table_loaded == 1) {
		return false;
	}
	window.round_table_loaded = 1;

	// ��ȡҳ���еı��Ԫ�أ�
	var ts = document.getElementsByTagName("TABLE");

	for (var i=(ts.length - 1); i>=0; i--) {
		var table = ts[i];
		var tc = table.className.split(" ");
		if (in_array("round_table", tc)) {
			var table_id = table.id;
			var width = byid(table_id+":width").value || "100%";
			var c = byid(table_id+":class").value;
			if (c && c != '') {
				c_arr = c.split(",");
				wee_add_round_table_top(table, c_arr[0], width);
				wee_add_round_table_bottom(table, c_arr[1], width);
			}
		}
	}
	// end for
}

function in_class(class_name, obj_class) {
	var obj_class_s = obj_class.split(" ");
	for (var i=0; i<obj_class_s.length; i++) {
		if (obj_class_s[i] == class_name) {
			return true;
		}
	}
	return false;
}

function add_class(o, new_class) {
	var s = o.className;
	o.className = s ? s+" "+new_class : new_class;
}

function remove_class(o, class_name) {
	var s = o.className;
	if (s == class_name) {
		o.className = '';
	} else {
		var s_s = s.split(" ");
		var new_class = [];
		for (var i=0; i<s_s.length; i++) {
			if (s_s[i] != class_name) {
				new_class.push(s_s[i]);
			}
		}
		o.className = new_class.join(" ");
	}
}

dom_loaded.load(wee_add_round_table);
