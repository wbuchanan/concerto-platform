\name{concerto.convertToNumeric}
\alias{concerto.convertToNumeric}
\title{
Numeric values
}
\description{
Converts any variable to numeric value without raising any warnings or errors.
}
\usage{
concerto.convertToNumeric(var)
}
\arguments{
  \item{var}{
Variable we want to convert to numeric.
}
}
\details{
}
\value{
If the conversion is possible it will return numeric equivalent of input variable. If it fails then it will return unchanged input variable.
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
input <- "123"
numericInput <- concerto.convertToNumeric(input)
}

\keyword{ numeric }
