; <?php exit; ?> DO NOT REMOVE THIS LINE
test_pdf2swf = 1
test_pdf2json = ""
allowcache = 1
splitmode = ""

path.doc = "/data/sites/2012.xyhui.com/data/uploads/"
path.pdf = "/data/document/pdf/"
path.swf = "/data/document/swf/"

cmd.conversion.singledoc = "\"/u/swftools/bin/pdf2swf\" \"{pdffile}\" -o \"{swffile}\" -f -T 9 -t -s storeallcharacters"

cmd.conversion.splitpages = "\"/u/swftools/bin/pdf2swf\" \"{path.pdf}{pdffile}\" -o \"{path.swf}{pdffile}%.swf\" -f -T 9 -t -s storeallcharacters -s linknameurl"

cmd.conversion.jodconverter = "\"/usr/bin/java\" -classpath /data/sites/2012.xyhui.com/apps/document/bin:/data/sites/2012.xyhui.com/apps/document/bin/* helloworld \"/usr/lib64/libreoffice\" \"{docfile}\"  \"{pdffile}\""

cmd.searching.extracttext = "\"/u/swftools/bin/swfstrings\" \"{path.swf}{swffile}\""
pdf2swf = 1
admin.username = "mudboy"
admin.password = 111111
licensekey = ""
cmd.conversion.renderpage = ""
cmd.conversion.rendersplitpage = ""
cmd.conversion.jsonfile = ""
