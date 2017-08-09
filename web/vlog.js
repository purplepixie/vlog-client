function vlog(config,container,autoload)
{
    this.config = null;
    this.container = container;
    this.autoload = autoload;
    var self = this;

    this.configLoad = function(code,data)
    {
        self.config = data;
        if (self.autoload)
            self.load();
    }

    this.itemsLoad = function(code,data)
    {
        var parent = document.getElementById(self.container);

        while(parent.firstChild)
            parent.removeChild(parent.firstChild);

        for(var i=0; i<data.count; ++i)
        {
            var dt = data.entries[i].recorded;
            var username = data.entries[i].username;
            var person = data.entries[i].person;
            var host = data.entries[i].host;
            var entry = data.entries[i].entry;

            var item = document.createElement("DIV");
            item.className = 'vlog-item';

            var detail = document.createElement("DIV");
            detail.className = 'vlog-detail';

            var dl = "<span class='vlog-detail-dt'>"
                + dt + "</span> <span class='vlog-detail-username'>"
                + username
                + "</span><span class='vlog-detail-at'>@</span><span class='vlog-detail-host'>"
                + host
                + " <span class='vlog-detail-bracket'>(</span><span class='vlog-detail-person'>"+person+"</span><span class='vlog-detail-bracket'>)</span>";

            detail.innerHTML = dl;
            item.appendChild(detail);

            var text = document.createElement("DIV");
            text.className = 'vlog-entry';
            text.innerHTML = entry.replace(/(?:\r\n|\r|\n)/g, '<br />');
            item.appendChild(text);

            parent.appendChild(item);
        }
    }

    this.getJSON = function(url, callback, params)
    {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.responseType = 'json';
        xhr.onload = function() {
          var status = xhr.status;
          if (status == 200) {
            callback(null, xhr.response);
          } else {
            callback(status);
          }
        };
        xhr.send(params);
    }

    this.load = function(n)
    {
        if (n==undefined)
            n=10;
        var p = "m=r&key="+self.config.key+"&limit="+n;
        self.getJSON(self.config.api, self.itemsLoad, p);
    }


    this.getJSON(config,this.configLoad);
}
