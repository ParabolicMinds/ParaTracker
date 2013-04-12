#!/usr/bin/ruby

Dir.glob("mp_*").each { |f| File.rename(f,f.gsub(/mp_/,"")) }
