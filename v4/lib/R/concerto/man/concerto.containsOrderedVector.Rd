\name{concerto.containsOrderedVector}
\alias{concerto.containsOrderedVector}
\title{
Ordered vectors
}
\description{
Checks if vector contains ordered vector.
}
\usage{
concerto.containsOrderedVector(subject, search)
}
\arguments{
  \item{subject}{
Vector in which we will search for another ordered vector.
}
  \item{search}{
Vector we will try to find in subject vector.
}
}
\details{
}
\value{
Logical: TRUE if "Haystack" vector contains "Needle" ordered vector.
}
\references{
}
\author{
Przemyslaw Lis
}
\note{
}

\seealso{
}
\examples{
haystack <- c(1,2,3,4)
needle <- c(2,4)
contains <- concerto.containsOrderedVector(haystack,needle)
#contains will be FALSE
}
% Add one or more standard keywords, see file 'KEYWORDS' in the
% R documentation directory.
\keyword{ vector }
\keyword{ ordered }
