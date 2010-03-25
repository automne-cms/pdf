#----------------------------------------------------------------
#
# Messages content for module cms_pdf
# French Messages
#
#----------------------------------------------------------------

DELETE FROM messages WHERE module_mes = 'cms_pdf' and language_mes = 'en';

INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(1, 'cms_pdf', 'en', 'PDF Export');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(2, 'cms_pdf', 'en', 'Page');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(3, 'cms_pdf', 'en', 'Table Of Content');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(4, 'cms_pdf', 'en', 'Created on %s');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(5, 'cms_pdf', 'en', '<div class="rowComment">\r\n<h1>Export a PDF of one or more pages:</h1>\r\n<span class="code"> &lt;atm-pdf-link&gt; ... <span class="keyword">{{href}}</span>  ... &lt;/atm-pdf-link&gt;&#160;</span>\r\n<ul>\r\n    <li><span class="keyword">{{href}} </span>: This value will be replaced by the address of the PDF produced. It is usually placed in the href attribute of a link.</li>\r\n</ul>\r\n<p>This tag supports the following optional attributes:</p>\r\n<ul>\r\n    <li><span class="keyword">title </span>: String. Specifies the title of the PDF. If this attribute does not exist, the exported page title will be used.</li>\r\n    <li><span class="keyword">subtitle </span>: String. Allows you to specify the subtitle of the PDF. If this attribute does not exist, the address of the current page will be used.</li>\r\n    <li><span class="keyword">page </span>: Integer. Specifies the page to export to PDF. If this attribute does not exist, the current page will be exported. This attribute is ignored if the attribute <span class="keyword">pages </span>exists.</li>\r\n    <li><span class="keyword">subpages </span>: Boolean. Export also the subpages of the exported page.</li>\r\n    <li><span class="keyword">pages </span>: List of pages identifiers separated by commas. Allows you to specify which pages to export to PDF. This attribute is ignored if the attribute <span class="keyword">subpages </span>exists.</li>\r\n    <li><span class="keyword">exclude </span>: List of pages identifiers separated by commas. Used to exclude one or more pages from export.</li>\r\n    <li><span class="keyword">crosswebsite </span>: Boolean. Lets cross the boundary of websites. This attribute is used if the attribute subpages is used. If this attribute does not exist, the export will be limited to pages of the current site.</li>\r\n    <li><span class="keyword">maxlevel </span>: Integer. Specifies the maximum number of levels deep. If this attribute does not exist, the export will have no depth limit.</li>\r\n    <li><span class="keyword">language </span>: String. Code language to be used as labels for the footer. If this attribute does not exist, the language of the current page will be used.</li>\r\n    <li><span class="keyword">toc </span>: Boolean. Add the table of contents for the generated PDF. If this attribute does not exist, the table of contents will automatically be added to multi-page PDF export.</li>\r\n    <li><span class="keyword">cache </span>: Integer. Duration of the caching of generated PDF in seconds. If this attribute does not exist, one hour (3600 seconds) will be used for caching. Specify 0 to disable caching. For performance reasons, disable the caching is not recommended for multi-page PDF export.</li>\r\n    <li><span class="keyword">keeprequest </span>: Boolean. Keeps GET datas of the current page to generate the PDF. This will generate a PDF containing dynamic datas identical to those of the page containing the tag atm-pdf-link.</li>\r\n</ul>\r\n<h1>Examples :</h1>\r\n<h3>PDF of the current page:</h3>\r\n<p><span class="code">&lt;atm-pdf-link&gt;&lt;a href="{{href}}"  target="_blank"&gt;Download PDF&lt;/a&gt;&lt;/atm-pdf-link&gt;</span></p>\r\n<h3>PDF of the page 3 and all sub pages except page 28:</h3>\r\n<p><span class="code">&lt;atm-pdf-link page="3" subpages="true"  excluded="28" title="PDF Title"&gt;&lt;a href="{{href}}"  target="_blank"&gt;Download PDF&lt;/a&gt;&lt;/atm-pdf-link&gt; </span></p>\r\n<h3>PDFof&#160; pages 3, 5 and 7:</h3>\r\n<p><span class="code">&lt;atm-pdf-link  pages="3,5,6" title="PDF Title"&gt;&lt;a href="{{href}}"  target="_blank"&gt;Download PDF&lt;/a&gt;&lt;/atm-pdf-link&gt;</span></p>\r\n</div>\r\n<p>&#160;</p>');