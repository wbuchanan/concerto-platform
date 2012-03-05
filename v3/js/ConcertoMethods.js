/*
Concerto Platform - Online Adaptive Testing Platform
Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; version 2
of the License, and not any of the later versions.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

function ConcertoMethods(){};

ConcertoMethods.loading=function(selector){
    $(selector).html("<div align='center' style='width:100%;height:100%;'><table style='width:100%;height:100%;'><tr><td valign='middle' align='center'><img src='css/img/ajax-loader.gif' /></td></tr></table></div>")  
};