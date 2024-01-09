# Change Log
In this version, the plugin deals with Fawry api V2 instead of V1 with the following changes to the code

1 - In the original code, there was no verification with Fawry server made in all cases (return from pay using CC, or callback). Now, all payment is verified before the order status updates.

2 - In the original code, the signature was computed with md5, now it is computed with sha256.

3 - In the original code, paying for the order was performed by clicking a button, but now it is redirected automatically after showing a spinner.

4- The original source code is full of PHP warnings. You can see this if you enable the debug mode in WordPress. But now, all warnings are resolved.

# myfawry_woocomerce
Fawry Pay woo-commerce plugin
### To install simply : 
1. download the zip file 
2. open wordpress[admin] plugins page
3. click add new 
4. click upload plugin, browse to the zipped file and choose it


