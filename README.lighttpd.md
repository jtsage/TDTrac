## TDTRAC Rewrite rules for lighttpd.

Pulled from a working installation.  Update your path and host as needed.

NOTE: You must have a .htaccess (even empty) in the root install directory for tdtrac to use URL rewriting

    $HTTP["host"] =~ "host\.tdtrac\.com" {
	  server.document-root = "/path/to/tdtrac/installation/"
        url.rewrite-once = (
          "/(.*)\.(.*)" => "$0",
          "^/([^.]+)$" => "/index.php?action=$1",
          "^/$" => "/index.php"
        )
    }

