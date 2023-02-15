resource "aws_instance" "meysam" {
  ami           = "ami-08cd358d745620807"
  instance_type = "t2.micro"
  key_name      = "dvh"

  tags = {
    Name       = "meysam"
    Created_By = "terraform"
  }
}
# resource "aws_instance" "babak2" {
#   ami = "ami-08cd358d745620807"

#   instance_type = "t2.micro"

#   tags = {
#     Created_By = "terraform"
#   }
# }
