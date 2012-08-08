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

QTImapResponse <- function(variableName){
    variable <- get(variableName)
    mapEntry <- get(paste(variableName,".mapping.mapEntry",sep=''))
    defaultValue <- get(paste(variableName,".mapping.defaultValue",sep=''))

    result <- 0
    for(v in unique(variable)){
        if(!is.na(mapEntry[v])) result <- result + mapEntry[v]
        else result <- result + defaultValue
    }
    if(exists(paste(variableName,".mapping.lowerBound",sep=''))){
        lowerBound <- get(paste(variableName,".mapping.lowerBound",sep=''))
        if(result<lowerBound) result <- lowerBound
    }
    if(exists(paste(variableName,".mapping.upperBound",sep=''))){
        upperBound <- get(paste(variableName,".mapping.upperBound",sep=''))
        if(result>upperBound) result <- upperBound
    }
    return(result)
}