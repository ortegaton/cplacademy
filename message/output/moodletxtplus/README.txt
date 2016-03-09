-----------------------------------------------
MOODLETXT+ SMS MESSAGE PROCESSOR FOR CONNECTTXT

Author:  Greg J Preece
Company: Blackboard ConnectTxt
Country: England
Contact: txttoolssupport@blackboard.com
Version: 1.0
Release Date: 19th October 2012
-----------------------------------------------


--WHAT IS IT?--

moodletxt+ is an SMS messaging processor for Moodle 2.0 and up. It receives
notification messages from the Moodle system (when configured by the user
to do so) and hands them off to the moodletxt block for sending. Sent messages
are stored and viewed within moodletxt, and can receive status updates as 
normal.


--INSTALLATION--

moodletxt+ requires the moodletxt SMS messaging block to be installed first.
You will need at least version 3.0 of the block. If you have not yet installed
moodletxt, you can find it, along with installation instructions, at this
address:

https://moodle.org/plugins/view.php?plugin=block_moodletxt

Installation for moodletxt+ is the same as for any other message processor:
simply unzip the installer into your Moodle installation's /message/output
directory, which should create a new "moodletxtplus" directory. 

Once you have done this, log into Moodle as an administrator and click
the Notifications link on the Site Administration menu. The automatic 
installation/upgrade scripts will do the rest.

When upgrading, it is recommended to remove the old moodletxt+ folder completely
and replace it with a fresh copy from the new installer, before running the
upgrade script. All data is held within the database, so you will not lose 
anything by doing this, and it helps prevent conflicts between versions.


--STAYING UP TO DATE--

As well as the Moodle plugin repository, news and updates on the plugin can
be found on our website, at the following address:

https://www.bbconnecttxt.com/preloginjsp/plugins.jsp

An RSS feed is available at:

https://www.bbconnecttxt.com/preloginjsp/moodletxt/rss.xml


--NEW IN VERSION 1.0--

* Initial release of processor. Simple pass-through messaging implementation.


--LICENCE--

moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
A full copy of this licence can be found @
http://www.gnu.org/licenses/gpl.html
In addition to this licence, as described in section 7, we add the following terms:
  - Derivative works must preserve original authorship attribution (@author tags and other such notices)
  - Derivative works do not have permission to use the trade and service names 
    "ConnectTxt", "txttools", "moodletxt", "moodletxt+", "Blackboard", "Blackboard Connect" or "Cy-nap"
  - Derivative works must be have their differences from the original material noted,
    and must not be misrepresentative of the origin of this material, or of the original service

Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
or legal liability arising from their use of this code.