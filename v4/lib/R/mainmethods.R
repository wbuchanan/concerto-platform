## 
## Concerto Platform - Online Adaptive Testing Platform
## Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University
##
## This program is free software; you can redistribute it and/or
## modify it under the terms of the GNU General Public License
## as published by the Free Software Foundation; version 2
## of the License, and not any of the later versions.
##
## This program is distributed in the hope that it will be useful,
## but WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
## GNU General Public License for more details.
##
## You should have received a copy of the GNU General Public License
## along with this program; if not, write to the Free Software
## Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
##


update.session.status <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `status` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.time_limit <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `time_limit` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.template_id <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_id` ='%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.HTML <- function(CONCERTO_PARAM1, CONCERTO_PARAM2, CONCERTO_PARAM3){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(fill.session.HTML(get.template.HTML(CONCERTO_PARAM1,CONCERTO_PARAM2,CONCERTO_PARAM3))))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `HTML` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}