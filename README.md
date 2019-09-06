
# IGAT #

IGAT - Interactive Gamification Analytics Tool is an moodle block plugin that adds gamification analytics features to your course. This plugin buils upon the moodle badge system and the gamification added by the "Level Up!" plugin. It allows indeph behavior analysis of students with different learning styles by using the "ALSTEA" plugin. 

## Features ##
* A gamification dashboard for the students - everything concerning the gamification is at one place
* Gamification analytics for teachers: Indeph statistics about the gamification that can be filtered by learning style
	* Gamification dashboard statistics: gamification tab views, tab view duration, subsequent pages to gamification dashboard, chosen leaderboard settings
	* Game elemtents analytics: gamification feedback rate, points distribution, level distribution, average time to earn badges and reach levels

## Requirements ##
* Moodle version 3.7.1
* [Level up!](https://moodle.org/plugins/block_xp) plugin version 2019161101
* ALSTEA plugin version 2018101302

## Installation and Configuration ##

1. Install "Level up!" plugin and add "Level up!" block to your course
2. Configure gamification in "Level up!" settings and particularly click "Save changes" in levels tab.
3. Install "ALSTEA" plugin and add it to your course
4. Download the latest release of IGAT or clone this repository into a folder "igat" and zip this folder 
5. Go to Site Administration->Plugins->Install Plugins in moodle and upload the zip to install this plugin
6. Add "IGAT" block to your course

Now, the level up block can be hidden in this course. The gamification configuration can be accessed via "Gamification Analytics"->Configuration. There is also a link for managing the badges. Always create a criteria description when setting badge criteria, as this description will be userd on the students IGAT block.

## License ##

2019 Manuel Gottschlich

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
